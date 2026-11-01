<?php

namespace Tests\Feature\Wallet;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PricingSetting;
use App\Models\PrintletRemittance;
use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class WalletMicroLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        PricingSetting::current(); // Ensure settings with payout_holding_days = 14
        $this->walletService = app(WalletService::class);
    }

    public function test_delivered_order_triggers_escrow_hold_for_artist_and_platform_cut(): void
    {
        $artist = User::factory()->create(['role' => 'artist']);
        $customerUser = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);

        $product = Product::factory()->create(['user_id' => $artist->id]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'status' => 'processing',
            'total_price' => 300.00,
            'shipping_amount' => 40.00,
            'payment_method' => 'instapay',
            'payment_status' => 'paid',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'artist_id' => $artist->id,
            'quantity' => 2,
            'unit_price' => 130.00,
            'print_cost' => 60.00,
            'artist_profit' => 45.00, // Total cut = 45 * 2 = 90.00
            'platform_cut' => 15.00,
            'platform_fixed_fee' => 10.00, // Total platform = (15 + 10) * 2 = 50.00
        ]);

        // Trigger delivery
        $order->update(['status' => 'delivered']);

        $artistWallet = Wallet::forUser($artist);
        $this->assertEquals(90.00, (float) $artistWallet->pending_balance);
        $this->assertEquals(0.00, (float) $artistWallet->balance);
        $this->assertEquals(0.00, (float) $artistWallet->total_paid_out);

        // Verify transaction micro-ledger
        $tx = WalletTransaction::where('wallet_id', $artistWallet->id)->first();
        $this->assertNotNull($tx);
        $this->assertEquals('credit', $tx->type);
        $this->assertEquals('order_escrow_hold', $tx->category);
        $this->assertEquals(90.00, (float) $tx->amount);
        $this->assertEquals('pending', $tx->status);
        $this->assertEquals(Order::class, $tx->reference_type);
        $this->assertEquals($order->id, $tx->reference_id);
        $this->assertNotNull($tx->hold_until);
        $this->assertTrue($tx->hold_until->isFuture());

        // Platform wallet check
        $platformWallet = Wallet::platform();
        $this->assertEquals(50.00, (float) $platformWallet->balance);
    }

    public function test_escrow_hold_is_idempotent(): void
    {
        $artist = User::factory()->create(['role' => 'artist']);
        $customerUser = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);
        $product = Product::factory()->create(['user_id' => $artist->id]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'delivered_at' => now(),
            'total_price' => 200.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'artist_id' => $artist->id,
            'quantity' => 1,
            'unit_price' => 200.00,
            'print_cost' => 80.00,
            'artist_profit' => 60.00,
            'platform_cut' => 30.00,
            'platform_fixed_fee' => 10.00,
        ]);

        // Explicitly call twice
        $this->walletService->holdOrderEarnings($order);
        $this->walletService->holdOrderEarnings($order);

        $artistWallet = Wallet::forUser($artist);
        $this->assertEquals(60.00, (float) $artistWallet->pending_balance);

        $txCount = WalletTransaction::where('wallet_id', $artistWallet->id)->count();
        $this->assertEquals(1, $txCount);
    }

    public function test_record_manual_artist_payout_deducts_pending_balance_and_records_transfer_reference(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $artist = User::factory()->create(['role' => 'artist']);

        $wallet = Wallet::forUser($artist);
        $wallet->update([
            'pending_balance' => 250.00,
            'payout_method' => 'instapay',
            'payout_details' => ['instapay_handle' => 'artist@instapay'],
        ]);

        $payoutTx = $this->walletService->recordManualArtistPayout(
            wallet: $wallet,
            amount: 250.00,
            transferReference: 'IPA-REF-998811',
            admin: $admin,
            notes: 'October settlement'
        );

        $wallet->refresh();
        $this->assertEquals(0.00, (float) $wallet->pending_balance);
        $this->assertEquals(250.00, (float) $wallet->total_paid_out);

        $this->assertEquals('manual_artist_payout', $payoutTx->category);
        $this->assertEquals('debit', $payoutTx->type);
        $this->assertEquals(250.00, (float) $payoutTx->amount);
        $this->assertEquals('IPA-REF-998811', $payoutTx->transfer_reference);
        $this->assertEquals($admin->id, $payoutTx->transferred_by);
        $this->assertEquals('completed', $payoutTx->status);
    }

    public function test_cannot_payout_more_than_pending_balance(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $admin = User::factory()->create(['role' => 'admin']);
        $artist = User::factory()->create(['role' => 'artist']);

        $wallet = Wallet::forUser($artist);
        $wallet->update(['pending_balance' => 100.00]);

        $this->walletService->recordManualArtistPayout(
            wallet: $wallet,
            amount: 200.00,
            transferReference: 'IPA-OVERDRAFT',
            admin: $admin
        );
    }

    public function test_order_cancellation_voids_pending_escrow(): void
    {
        $artist = User::factory()->create(['role' => 'artist']);
        $customerUser = User::factory()->create(['role' => 'customer']);
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);
        $product = Product::factory()->create(['user_id' => $artist->id]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
            'delivered_at' => now(),
            'total_price' => 200.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'artist_id' => $artist->id,
            'quantity' => 1,
            'unit_price' => 200.00,
            'artist_profit' => 75.00,
        ]);

        $this->walletService->holdOrderEarnings($order);

        $wallet = Wallet::forUser($artist);
        $this->assertEquals(75.00, (float) $wallet->pending_balance);

        // Customer returns / cancels order
        $order->update(['status' => 'cancelled']);

        $wallet->refresh();
        $this->assertEquals(0.00, (float) $wallet->pending_balance);

        $holdTx = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('category', 'order_escrow_hold')
            ->first();

        $this->assertEquals('cancelled', $holdTx->status);
    }

    public function test_record_printlet_remittance(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $remittance = $this->walletService->recordPrintletRemittance(
            amount: 4500.00,
            remittanceDate: '2026-10-31',
            referenceNumber: 'PRNT-DEPOSIT-8831',
            admin: $admin,
            notes: 'Weekly Cairo COD courier settlement'
        );

        $this->assertDatabaseHas('printlet_remittances', [
            'amount' => 4500.00,
            'reference_number' => 'PRNT-DEPOSIT-8831',
            'status' => 'received',
            'recorded_by' => $admin->id,
        ]);
    }
}
