<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds Printlet external order, quote, and status tracking columns to the orders table.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('printlet_order_id')->nullable()->index()->after('id')
                ->comment('Unique order ID returned by Printlet Fulfillment API');
            $table->string('printlet_quote_id')->nullable()->after('printlet_order_id')
                ->comment('Quote ID associated with the Printlet order creation');
            $table->string('printlet_status')->default('pending_verification')->after('status')
                ->comment('Normalized status from Printlet API e.g. pending_verification, accepted, in_production, shipped, delivered, cancelled');
            $table->string('cod_reference')->nullable()->after('printlet_status')
                ->comment('Reference string supplied during manual COD verification step');
            $table->decimal('shipping_amount', 10, 2)->default(0.00)->after('total_price')
                ->comment('Fulfillment / shipping cost returned by Printlet quote API');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'printlet_order_id',
                'printlet_quote_id',
                'printlet_status',
                'cod_reference',
                'shipping_amount',
            ]);
        });
    }
};
