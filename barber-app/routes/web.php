<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;

// Rutas Cliente
Route::get('/', [BookingController::class, 'index'])->name('booking.index');
Route::get('/api/booking/data', [BookingController::class, 'getData']);
Route::get('/api/booking/availability', [BookingController::class, 'getAvailability']);
Route::post('/api/booking/store', [BookingController::class, 'store']);

// Rutas Admin
Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->name('admin.authenticate');
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::post('/admin/appointment/{appointment}/status', [AdminController::class, 'updateAppointmentStatus'])->name('admin.appointment.update');

Route::post('/admin/barber', [AdminController::class, 'storeBarber'])->name('admin.barber.store');
Route::post('/admin/barber/{barber}/delete', [AdminController::class, 'destroyBarber'])->name('admin.barber.destroy');

Route::post('/admin/service', [AdminController::class, 'storeService'])->name('admin.service.store');
Route::post('/admin/service/{service}/delete', [AdminController::class, 'destroyService'])->name('admin.service.destroy');

Route::post('/admin/membership', [AdminController::class, 'storeMembership'])->name('admin.membership.store');
Route::post('/admin/membership/{membership}/delete', [AdminController::class, 'destroyMembership'])->name('admin.membership.destroy');
