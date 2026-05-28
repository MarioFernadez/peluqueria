<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ReportController;

// ─── Rutas Públicas (Cliente) ───────────────────────────────────────────────
Route::get('/', [BookingController::class, 'index'])->name('booking.index');
Route::get('/api/booking/data', [BookingController::class, 'getData']);
Route::get('/api/booking/availability', [BookingController::class, 'getAvailability']);
Route::post('/api/booking/store', [BookingController::class, 'store']);

// ─── Auth Admin ─────────────────────────────────────────────────────────────
Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->name('admin.authenticate');
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// ─── Panel Admin ────────────────────────────────────────────────────────────
Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');

// Citas
Route::post('/admin/appointment/{appointment}/status', [AdminController::class, 'updateAppointmentStatus'])->name('admin.appointment.update');

// Barberos
Route::post('/admin/barber', [AdminController::class, 'storeBarber'])->name('admin.barber.store');
Route::post('/admin/barber/{barber}/delete', [AdminController::class, 'destroyBarber'])->name('admin.barber.destroy');

// Servicios
Route::post('/admin/service', [AdminController::class, 'storeService'])->name('admin.service.store');
Route::post('/admin/service/{service}/delete', [AdminController::class, 'destroyService'])->name('admin.service.destroy');

// Membresías (planes)
Route::post('/admin/membership', [AdminController::class, 'storeMembership'])->name('admin.membership.store');
Route::post('/admin/membership/{membership}/delete', [AdminController::class, 'destroyMembership'])->name('admin.membership.destroy');

// ─── Clientes ────────────────────────────────────────────────────────────────
Route::get('/admin/clients', [ClientController::class, 'index'])->name('admin.clients.index');
Route::post('/admin/clients', [ClientController::class, 'store'])->name('admin.clients.store');
Route::get('/admin/clients/{client}', [ClientController::class, 'show'])->name('admin.clients.show');
Route::post('/admin/clients/{client}/update', [ClientController::class, 'update'])->name('admin.clients.update');
Route::post('/admin/clients/{client}/delete', [ClientController::class, 'destroy'])->name('admin.clients.destroy');
Route::post('/admin/clients/{client}/membership', [ClientController::class, 'addMembership'])->name('admin.clients.membership');

// ─── Finanzas ────────────────────────────────────────────────────────────────
Route::get('/admin/finance', [FinanceController::class, 'index'])->name('admin.finance.index');
Route::get('/admin/finance/payments', [FinanceController::class, 'payments'])->name('admin.finance.payments');

// ─── Reportes ────────────────────────────────────────────────────────────────
Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports.index');

// ─── API REST ────────────────────────────────────────────────────────────────
Route::prefix('api')->group(function () {
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/finance/summary', [FinanceController::class, 'index']);
    Route::get('/finance/payments', [FinanceController::class, 'payments']);
});
