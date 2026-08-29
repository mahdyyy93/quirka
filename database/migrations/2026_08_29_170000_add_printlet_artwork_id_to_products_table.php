<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds Printlet Artwork external ID and revision tracking to the products table.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('printlet_artwork_id')->nullable()->after('sku')
                ->comment('External artwork ID generated/stored on Printlet Fulfillment API');
            $table->unsignedInteger('printlet_artwork_revision')->default(1)->after('printlet_artwork_id')
                ->comment('Revision integer for the Printlet artwork resource');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['printlet_artwork_id', 'printlet_artwork_revision']);
        });
    }
};
