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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User who performed the stock movement
            $table->enum('type', ['in', 'out', 'adjustment']); // Added 'adjustment' type
            $table->integer('quantity');
            $table->date('date');
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('reason')->nullable(); // Changed from note to reason
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
