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
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('address');
        });

        // Seed the Guest Walk-in customer
        DB::table('customers')->insert([
            'name' => 'Guest Walk-in',
            'email' => 'guest@system.local',
            'phone' => '0000000000',
            'address' => 'System Record',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('customers')->where('is_system', true)->delete();
        
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('is_system');
        });
    }
};
