<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UnitController;

use App\Http\Controllers\KhaltiController;
use App\Livewire\CreateSale;
use Illuminate\Support\Facades\Route;

// Public welcome page
Route::get('/', function () {
    return view('welcome');
});

// Dashboard (Jetstream Protected)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {

    // Admin + Staff Shared Routes (Match these first)
    Route::middleware(['role:admin,staff'])->group(function () {
        Route::get('/products', \App\Livewire\ManageProducts::class)->name('products.index');
        Route::get('/stock-movements', \App\Livewire\ManageStockMovements::class)->name('stock-movements.index');
        Route::get('/sales/create', \App\Livewire\ManageSale::class)->name('sales.create');
        Route::get('/sales', \App\Livewire\ManageSalesHistory::class)->name('sales.index'); 
        Route::get('/sales/{sale}', [App\Http\Controllers\SaleController::class, 'show'])->name('sales.show');
        Route::get('/sales/{sale}/download-pdf', [App\Http\Controllers\SaleController::class, 'downloadPdf'])->name('sales.download-pdf');


    });



    // Khalti Routes (Student Project Integration)
    Route::get('/khalti/initiate/{sale_id}', [\App\Http\Controllers\PaymentController::class, 'initiateKhalti'])->name('khalti.initiate');
    Route::get('/khalti/return', [\App\Http\Controllers\PaymentController::class, 'khaltiReturn'])->name('khalti.return');
    // REMOVED OLD ROUTES: verify and callback used by JS widget


    // Admin specialized routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', \App\Livewire\ManageUsers::class)->name('users.index'); 
        
        Route::resource('products', ProductController::class)->except(['index']);
        Route::get('/customers', \App\Livewire\ManageCustomers::class)->name('customers.index');
        Route::get('/suppliers', \App\Livewire\ManageSuppliers::class)->name('suppliers.index');
        Route::get('/categories', \App\Livewire\ManageCategories::class)->name('categories.index');
        Route::get('/brands', \App\Livewire\ManageBrands::class)->name('brands.index');
        Route::get('/units', \App\Livewire\ManageUnits::class)->name('units.index');


        Route::get('/purchases/create', \App\Livewire\ManagePurchase::class)->name('purchases.create');
        Route::get('/purchases/{purchase}/edit', \App\Livewire\ManagePurchase::class)->name('purchases.edit');
        Route::resource('purchases', PurchaseController::class)->except(['create', 'edit']);
        Route::resource('stock-movements', StockMovementController::class)->except(['index', 'show', 'edit']); 
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/inventory', [ReportController::class, 'inventoryReport'])->name('reports.inventory');
        Route::get('/reports/purchases', [ReportController::class, 'purchaseReport'])->name('reports.purchases');
        Route::get('/payments/dashboard', \App\Livewire\PaymentDashboard::class)->name('payments.dashboard');
        
        // Wildcard routes last to prevent shadowing
        Route::get('/sales/{sale}/edit', \App\Livewire\ManageSale::class)->name('sales.edit');
        Route::delete('/sales/{sale}', [App\Http\Controllers\SaleController::class, 'destroy'])->name('sales.destroy');
    });
});
