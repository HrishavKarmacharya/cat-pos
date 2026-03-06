<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetApplicationData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset {--full : Clear master data (products, categories, etc.) as well}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely reset application data while preserving system records and RBAC';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('Are you sure you want to reset the application data? This action cannot be undone!')) {
            $this->info('Reset cancelled.');
            return;
        }

        $full = $this->option('full');

        $this->info('Starting data reset...');

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        // 1. Transactional Tables (Always Reset)
        $transactionalTables = [
            'sale_items',
            'sales',
            'purchase_items',
            'purchases',
            'stock_movements',
        ];

        foreach ($transactionalTables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                \Illuminate\Support\Facades\DB::table($table)->truncate();
                $this->line("Truncated transactional table: {$table}");
            }
        }

        // 2. Clear Non-System Customers
        \Illuminate\Support\Facades\DB::table('customers')->where('is_system', false)->delete();
        $this->line('Cleared non-system customers.');

        // 3. Ensure Guest Walk-in remains
        if (!\App\Models\Customer::where('is_system', true)->exists()) {
             \App\Models\Customer::create([
                'name' => 'Guest Walk-in',
                'email' => 'guest@system.local',
                'phone' => '0000000000',
                'address' => 'System Record',
                'is_system' => true,
            ]);
            $this->line('Re-seeded Guest Walk-in customer.');
        }

        if ($full) {
            // 4. Master Data Reset
            $masterTables = [
                'products',
                'categories',
                'brands',
                'units',
                'suppliers',
            ];

            foreach ($masterTables as $table) {
                if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                    \Illuminate\Support\Facades\DB::table($table)->truncate();
                    $this->line("Truncated master data: {$table}");
                }
            }
            $this->info('All master data cleared successfully.');
        } else {
            // 5. Reset Product Stock (If products are kept but movements are gone)
            \App\Models\Product::query()->update(['stock_quantity' => 0]);
            $this->line('Reset all product stock quantities to zero.');
        }

        // 6. Housekeeping
        $housekeepingTables = ['cache', 'jobs', 'failed_jobs', 'sessions'];
        foreach ($housekeepingTables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                \Illuminate\Support\Facades\DB::table($table)->truncate();
            }
        }

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $this->info('Successfully reset application data!');
        $this->warn('System users, roles, and migration history have been preserved.');
    }
}
