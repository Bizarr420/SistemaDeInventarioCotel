<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
use App\Http\Controllers\{
    DashboardController, ProductController, CategoryController,
    SupplierController, MovementController, ReportController
};

Route::get('/', fn() => redirect()->route('dashboard'))->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);

    // Movimientos de inventario
    Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
    Route::post('/movements/in', [MovementController::class, 'storeIn'])->name('movements.in');
    Route::post('/movements/out', [MovementController::class, 'storeOut'])->name('movements.out');

    // Reportes
    Route::get('/reports/summary', [ReportController::class, 'summary'])->name('reports.summary');
    Route::get('/reports/kardex', [ReportController::class, 'kardex'])->name('reports.kardex');
});

require __DIR__.'/auth.php'; // Breeze
