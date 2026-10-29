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
        Schema::create('pricing_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('artist_profit_floor', 10, 2)->default(20.00);
            $table->decimal('artist_profit_ceiling', 10, 2)->default(200.00);
            $table->decimal('platform_percent', 5, 4)->default(0.1000); // 10%
            $table->decimal('platform_fixed_fee_per_item', 10, 2)->default(10.00);
            $table->decimal('rounding_step', 10, 2)->default(5.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_settings');
    }
};
