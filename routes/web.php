<?php

use App\Http\Controllers\AccountingAdjustmentController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AssetVerificationController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CosoControlController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DictamenController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\FixedAssetMovementController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\ObsoletenessCriterionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SecuritySettingsController;
use App\Http\Controllers\RiskAssessmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'))->name('home');

Route::middleware(['auth', 'verified', 'active.account'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard.view');

    Route::resource('inventory', InventoryController::class)->middleware('permission:inventory.view');

    Route::resource('fixed-assets', FixedAssetController::class)->middleware('permission:assets.view');
    Route::get('fixed-assets/acquisitions/create', [FixedAssetMovementController::class, 'createAcquisition'])
        ->name('fixed-assets.acquisitions.create');
    Route::post('fixed-assets/acquisitions', [FixedAssetMovementController::class, 'acquire'])
        ->name('fixed-assets.acquisitions.store');
    Route::post('fixed-assets/{fixed_asset}/movements', [FixedAssetMovementController::class, 'store'])
        ->name('fixed-assets.movements.store');
    Route::get('fixed-assets-export/excel', [FixedAssetController::class, 'exportExcel'])->name('fixed-assets.export.excel');
    Route::get('fixed-assets-export/pdf', [FixedAssetController::class, 'exportPdf'])->name('fixed-assets.export.pdf');
    Route::patch('fixed-assets/{fixed_asset}/dispose', [FixedAssetMovementController::class, 'dispose'])
        ->name('fixed-assets.dispose');
    Route::get('fixed-assets-migration', [FixedAssetController::class, 'migrationCreate'])->name('fixed-assets.migration.create');
    Route::post('fixed-assets-migration/preview', [FixedAssetController::class, 'migrationPreview'])->name('fixed-assets.migration.preview');
    Route::post('fixed-assets-migration/store', [FixedAssetController::class, 'migrationStore'])->name('fixed-assets.migration.store');
    Route::get('fixed-assets-verifications', [AssetVerificationController::class, 'index'])->name('fixed-assets.verifications.index');
    Route::get('fixed-assets-verifications/create', [AssetVerificationController::class, 'create'])->name('fixed-assets.verifications.create');
    Route::post('fixed-assets-verifications', [AssetVerificationController::class, 'store'])->name('fixed-assets.verifications.store');

    Route::resource('products', ProductController::class);
    Route::post('/products/sync-spring', [ProductController::class, 'syncSpring'])->name('products.syncSpring');

    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('warehouses', WarehouseController::class)->except(['show']);

    Route::resource('users', UserController::class)->except(['show'])->middleware('permission:users.manage');
    Route::post('users/{user}/password-reset', [UserController::class, 'sendPasswordReset'])
        ->name('users.password-reset')->middleware('permission:users.manage');
    Route::get('user-sessions', [UserController::class, 'sessions'])
        ->name('users.sessions')->middleware('permission:users.manage');
    Route::post('user-sessions/revoke-others', [UserController::class, 'revokeOtherSessions'])
        ->name('users.sessions.revoke-others')->middleware('permission:users.manage');
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:roles.manage');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:roles.manage');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:roles.manage');
    Route::get('security/settings', [SecuritySettingsController::class, 'index'])
        ->name('security.settings')->middleware('permission:security.manage');
    Route::put('security/settings', [SecuritySettingsController::class, 'update'])
        ->name('security.settings.update')->middleware('permission:security.manage');

    Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
    Route::post('/movements', [MovementController::class, 'store'])->name('movements.store');
    Route::get('/movements/{movement}', [MovementController::class, 'show'])->name('movements.show');
    Route::patch('/movements/{movement}/liquidate', [MovementController::class, 'liquidate'])->name('movements.liquidate');
    Route::post('/transfers', [MovementController::class, 'storeTransfer'])->name('transfers.store');
    Route::patch('/transfers/{transfer}/receive', [MovementController::class, 'receiveTransfer'])->name('transfers.receive');
    Route::patch('/transfers/{transfer}/cancel', [MovementController::class, 'cancelTransfer'])->name('transfers.cancel');

    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::patch('/alerts/{alert}/read', [AlertController::class, 'markAsRead'])->name('alerts.markAsRead');

    Route::resource('obsoleteness_criteria', ObsoletenessCriterionController::class)->middleware('permission:assets.criteria.manage');

    Route::get('/dictamens', [DictamenController::class, 'index'])->name('dictamens.index')->middleware('permission:assets.view');
    Route::post('/dictamens', [DictamenController::class, 'store'])->name('dictamens.store')->middleware('permission:dictamens.create');
    Route::get('/dictamens/create/{fixedAsset}', [DictamenController::class, 'create'])->name('dictamens.create')->middleware('permission:dictamens.create');
    Route::patch('/dictamens/{dictamen}/approve', [DictamenController::class, 'approve'])->name('dictamens.approve')->middleware('permission:dictamens.approve');

    Route::get('/reports/summary', [ReportController::class, 'summary'])->name('reports.summary')->middleware('permission:reports.view');
    Route::get('/reports/kardex', [ReportController::class, 'kardex'])->name('reports.kardex')->middleware('permission:reports.view');
    Route::get('/reports/deterioration', [ReportController::class, 'deterioration'])->name('reports.deterioration')->middleware('permission:reports.view');
    Route::get('/reports/comparative', [ReportController::class, 'comparative'])->name('reports.comparative')->middleware('permission:reports.view');
    Route::get('/reports/financial-notes', [ReportController::class, 'financialNotes'])->name('reports.financial-notes')->middleware('permission:reports.financial');
    Route::post('/reports/financial-notes/print', [ReportController::class, 'logFinancialNotesPrint'])
        ->name('reports.financial-notes.print')->middleware('permission:reports.print');
    Route::get('/reports/obsolescence/export', [ReportController::class, 'exportObsolescence'])->name('reports.obsolescence.export')->middleware('permission:reports.export');
    Route::get('/accounting-adjustments', [AccountingAdjustmentController::class, 'index'])->name('accounting-adjustments.index');
    Route::get('/accounting-adjustments/export', [AccountingAdjustmentController::class, 'export'])->name('accounting-adjustments.export');
    Route::get('/sicat/overview', [DashboardController::class, 'sicatOverview'])->name('sicat.overview')->middleware('permission:reports.view');
    Route::get('/sicat/overview/export', [DashboardController::class, 'exportSicatOverview'])->name('sicat.overview.export')->middleware('permission:reports.export');
    Route::get('/sicat/executive', [DashboardController::class, 'sicatExecutive'])->name('sicat.executive')->middleware('permission:reports.view');

    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index')->middleware('permission:audit.view');
    Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('audit_logs.export')->middleware('permission:audit.export');
    Route::get('/audit-logs/entity/{entityType}/{entityId}', [AuditLogController::class, 'entityHistory'])
        ->name('audit_logs.entity')->middleware('permission:audit.view');
    Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])
        ->name('audit_logs.show')->middleware('permission:audit.view');

    Route::prefix('/coso')->name('coso.')->middleware('permission:coso.manage')->group(function () {
        Route::get('/dashboard', [CosoControlController::class, 'dashboard'])->name('dashboard');
        Route::resource('/controls', CosoControlController::class);
        Route::resource('/risks', RiskAssessmentController::class);
        Route::get('/risks/dashboard', [RiskAssessmentController::class, 'dashboard'])->name('risks.dashboard');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
