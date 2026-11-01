<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index(); // 'credit', 'debit'
            $table->string('category')->index(); // 'order_escrow_hold', 'manual_artist_payout', 'order_escrow_cancel', 'platform_fee_collected', 'admin_adjustment'
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2)->default(0.00);
            $table->decimal('balance_after', 12, 2)->default(0.00);
            $table->decimal('pending_balance_before', 12, 2)->default(0.00);
            $table->decimal('pending_balance_after', 12, 2)->default(0.00);
            $table->string('status')->default('completed')->index(); // 'pending', 'cleared', 'cancelled', 'completed'
            $table->nullableMorphs('reference'); // e.g. App\Models\Order
            $table->string('transfer_reference')->nullable()->index(); // e.g. Instapay Ref number
            $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete(); // Admin who did the manual transfer
            $table->timestamp('hold_until')->nullable()->index(); // maturity date (10-14 days)
            $table->timestamp('cleared_at')->nullable();
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['wallet_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
