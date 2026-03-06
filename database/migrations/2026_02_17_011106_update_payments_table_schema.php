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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('pidx')->nullable()->change();
            $table->string('gateway')->nullable()->after('sale_id');
            $table->string('khalti_pidx')->nullable()->after('gateway');
            $table->string('khalti_transaction_id')->nullable()->after('khalti_pidx');
            $table->string('transaction_uuid')->nullable()->after('khalti_transaction_id');
            $table->string('order_id')->nullable()->after('transaction_uuid');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('amount');
            $table->decimal('total_amount', 10, 2)->default(0)->after('tax_amount');
            $table->text('raw_response')->nullable()->after('transaction_id');
            $table->timestamp('paid_at')->nullable()->after('raw_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'gateway',
                'khalti_pidx',
                'khalti_transaction_id',
                'transaction_uuid',
                'order_id',
                'tax_amount',
                'total_amount',
                'raw_response',
                'paid_at'
            ]);
        });
    }
};
