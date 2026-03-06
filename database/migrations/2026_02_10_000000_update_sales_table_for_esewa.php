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
            // Drop old fields if they exist (from previous incomplete implementation)
            if (Schema::hasColumn('sales', 'esewa_transaction_id')) {
                $table->dropColumn('esewa_transaction_id');
            }
            if (Schema::hasColumn('sales', 'payment_timestamp')) {
                $table->dropColumn('payment_timestamp');
            }

            // Add new fields
            $table->string('transaction_uuid')->nullable()->unique()->after('payment_status');
            $table->string('esewa_ref_id')->nullable()->after('transaction_uuid');
            $table->timestamp('paid_at')->nullable()->after('esewa_ref_id');
            
            // Ensure payment_method can be 'esewa' and payment_status is 'pending' by default
            // These might already be true but let's ensure consistency
            $table->string('payment_status')->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['transaction_uuid', 'esewa_ref_id', 'paid_at']);
            
            // Re-add old fields if needed for rollback (though usually not necessary for custom features)
            $table->string('esewa_transaction_id')->nullable();
            $table->timestamp('payment_timestamp')->nullable();
        });
    }
};
