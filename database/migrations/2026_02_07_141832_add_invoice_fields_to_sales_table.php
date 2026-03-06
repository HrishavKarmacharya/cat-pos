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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('invoice_number')->unique()->nullable()->after('id');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('total_amount');
            $table->decimal('subtotal', 12, 2)->default(0)->after('sale_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'tax_amount', 'subtotal']);
        });
    }
};
