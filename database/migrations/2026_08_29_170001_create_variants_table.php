<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the variants table for product size/color options and maps them to Printlet catalog IDs.
     */
    public function up(): void
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('size')->comment('Size specification e.g., 30x40cm, 50x70cm');
            $table->string('color')->comment('Frame color e.g., Black, White, Oak');
            $table->decimal('price', 10, 2)->comment('Storefront pricing, uniform across colors of same size');
            $table->string('sku')->unique()->comment('Internal merchant SKU for the specific size/color combination');

            // Printlet API catalog reference fields
            $table->string('printlet_variant_id')->comment('External variant ID in Printlet catalog');
            $table->unsignedInteger('printlet_variant_revision')->default(1)->comment('Printlet variant revision number');
            $table->string('printlet_print_area_id')->nullable()->comment('Printlet print area ID');
            $table->unsignedInteger('printlet_print_area_revision')->default(1);
            $table->string('printlet_technique_id')->nullable()->comment('Printlet printing technique ID');
            $table->unsignedInteger('printlet_technique_revision')->default(1);
            $table->string('placement_preset')->default('center')->comment('Placement preset e.g. center, fit');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
