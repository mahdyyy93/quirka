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
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->foreignId('artist_id')->nullable()->after('variant_id')->constrained('users')->nullOnDelete();
            $table->decimal('print_cost', 10, 2)->nullable()->after('unit_price');
            $table->decimal('artist_profit', 10, 2)->nullable()->after('print_cost');
            $table->decimal('platform_cut', 10, 2)->nullable()->after('artist_profit');
            $table->decimal('platform_fixed_fee', 10, 2)->nullable()->after('platform_cut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['variant_id']);
            $table->dropForeign(['artist_id']);
            $table->dropColumn([
                'variant_id',
                'artist_id',
                'print_cost',
                'artist_profit',
                'platform_cut',
                'platform_fixed_fee',
            ]);
        });
    }
};
