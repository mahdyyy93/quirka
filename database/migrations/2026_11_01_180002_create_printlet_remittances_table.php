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
        Schema::create('printlet_remittances', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 12, 2);
            $table->date('remittance_date')->index();
            $table->string('reference_number')->index();
            $table->string('status')->default('received')->index(); // 'pending', 'received', 'disputed'
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printlet_remittances');
    }
};
