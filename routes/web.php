<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AssetVerificationController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CosoControlController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DictamenController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ObsoletenessCriterionController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RiskAssessmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Inventory Routes (Services, routers, cables, etc.)
    Route::resource('inventory', InventoryController::class);

    // Fixed Assets Routes (Computers, equipment, etc.)
    Route::resource('fixed-assets', FixedAssetController::class);
    Route::get('fixed-assets-migration', [FixedAssetController::class, 'migrationCreate'])->name('fixed-assets.migration.create');
    Route::post('fixed-assets-migration/preview', [FixedAssetController::class, 'migrationPreview'])->name('fixed-assets.migration.preview');
    Route::post('fixed-assets-migration/store', [FixedAssetController::class, 'migrationStore'])->name('fixed-assets.migration.store');
    Route::get('fixed-assets-verifications', [AssetVerificationController::class, 'index'])->name('fixed-assets.verifications.index');
    Route::get('fixed-assets-verifications/create', [AssetVerificationController::class, 'create'])->name('fixed-assets.verifications.create');
    Route::post('fixed-assets-verifications', [AssetVerificationController::class, 'store'])->name('fixed-assets.verifications.store');

    // Legacy products route for backward compatibility
    Route::resource('products', ProductController::class);
    Route::post('/products/sync-spring', [ProductController::class, 'syncSpring'])->name('products.syncSpring');
    
    // Categories, Suppliers, Warehouses
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('warehouses', WarehouseController::class)->except(['show']);

    // User Management (Admin only)
    Route::resource('users', UserController::class)->middleware('role:admin');

    Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
    Route::post('/movements', [MovementController::class, 'store'])->name('movements.store');

    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::patch('/alerts/{alert}/read', [AlertController::class, 'markAsRead'])->name('alerts.markAsRead');

    Route::resource('obsoleteness_criteria', ObsoletenessCriterionController::class)->middleware('role:tecnico,contable,auditor');

    Route::resource('dictamens', DictamenController::class)->except(['show', 'edit', 'update', 'destroy']);
    Route::get('/dictamens/create/{product}', [DictamenController::class, 'create'])->name('dictamens.create')->middleware('role:tecnico');
    Route::patch('/dictamens/{dictamen}/approve', [DictamenController::class, 'approve'])->name('dictamens.approve')->middleware('role:contable,auditor');

    Route::get('/reports/summary', [ReportController::class, 'summary'])->name('reports.summary');
    Route::get('/reports/kardex', [ReportController::class, 'kardex'])->name('reports.kardex');
    Route::get('/reports/deterioration', [ReportController::class, 'deterioration'])->name('reports.deterioration');
    Route::get('/reports/comparative', [ReportController::class, 'comparative'])->name('reports.comparative');
    Route::get('/reports/obsolescence/export', [ReportController::class, 'exportObsolescence'])->name('reports.obsolescence.export');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index')->middleware('role:auditor,admin');

    // COSO 2013 Framework Routes
    Route::prefix('/coso')->name('coso.')->middleware('role:auditor,admin')->group(function () {
        Route::get('/dashboard', [CosoControlController::class, 'dashboard'])->name('dashboard');
        Route::resource('/controls', CosoControlController::class);
        Route::resource('/risks', RiskAssessmentController::class);
        Route::get('/risks/dashboard', [RiskAssessmentController::class, 'dashboard'])->name('risks.dashboard');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
