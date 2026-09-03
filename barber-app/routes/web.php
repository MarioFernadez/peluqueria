<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ReportController;

// ─── Rutas Públicas (Cliente) ───────────────────────────────────────────────
Route::get('/', [BookingController::class, 'index'])->name('home');
Route::get('/booking', [BookingController::class, 'booking'])->name('booking.index');
Route::get('/api/booking/data', [BookingController::class, 'getData']);
Route::get('/api/booking/availability', [BookingController::class, 'getAvailability']);
Route::get('/api/booking/blocked-dates', [BookingController::class, 'getBlockedDates']);
Route::post('/api/booking/store', [BookingController::class, 'store']);
Route::post('/api/booking/cancel', [BookingController::class, 'cancel']);
Route::post('/api/booking/notify-admin', [BookingController::class, 'notifyAdmin']);
Route::get('/admin/booking/weekly-availability', [BookingController::class, 'getWeeklyAvailability'])->name('admin.booking.weekly-availability');
Route::post('/admin/booking/toggle-weekly-slot', [BookingController::class, 'toggleWeeklySlot'])->name('admin.booking.toggle-weekly-slot');

// ─── Auth Admin ─────────────────────────────────────────────────────────────
Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'authenticate'])->name('admin.authenticate');
Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// ─── Panel Admin ────────────────────────────────────────────────────────────
Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::post('/admin/push-subscribe', [AdminController::class, 'pushSubscribe'])->name('admin.push-subscribe');

// ─── Panel Barbero ──────────────────────────────────────────────────────────
Route::get('/barber', [\App\Http\Controllers\BarberDashboardController::class, 'dashboard'])->name('barber.dashboard');
Route::post('/barber/schedule', [\App\Http\Controllers\BarberDashboardController::class, 'updateSchedule'])->name('barber.schedule.update');

// Apariencia
Route::get('/admin/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('admin.settings.index');
Route::post('/admin/settings/update', [\App\Http\Controllers\SettingController::class, 'update'])->name('admin.settings.update');
Route::post('/admin/settings/gallery', [\App\Http\Controllers\SettingController::class, 'storeGallery'])->name('admin.settings.gallery.store');
Route::post('/admin/settings/gallery/{id}/update', [\App\Http\Controllers\SettingController::class, 'updateGallery'])->name('admin.settings.gallery.update');
Route::post('/admin/settings/gallery/{id}/delete', [\App\Http\Controllers\SettingController::class, 'destroyGallery'])->name('admin.settings.gallery.destroy');

// Citas
Route::get('/admin/appointments', [AdminController::class, 'appointments'])->name('admin.appointments.index');
Route::post('/admin/appointment/{appointment}/status', [AdminController::class, 'updateAppointmentStatus'])->name('admin.appointment.update');
Route::post('/admin/appointment/{appointment}/delete', [AdminController::class, 'destroyAppointment'])->name('admin.appointment.destroy');

// Barberos
Route::get('/admin/barbers', [AdminController::class, 'barbers'])->name('admin.barbers.index');
Route::post('/admin/barber', [AdminController::class, 'storeBarber'])->name('admin.barber.store');
Route::post('/admin/barber/{barber}/update', [AdminController::class, 'updateBarber'])->name('admin.barber.update');
Route::post('/admin/barber/{barber}/delete', [AdminController::class, 'destroyBarber'])->name('admin.barber.destroy');
Route::get('/admin/barber/{barber}/schedule', [AdminController::class, 'barberSchedule'])->name('admin.barber.schedule');
Route::post('/admin/barber/{barber}/schedule', [AdminController::class, 'updateBarberSchedule'])->name('admin.barber.schedule.update');

// Días Bloqueados (Excepciones)
Route::get('/admin/blocked-dates', [AdminController::class, 'blockedDates'])->name('admin.blocked_dates.index');
Route::post('/admin/blocked-dates', [AdminController::class, 'storeBlockedDate'])->name('admin.blocked_dates.store');
Route::post('/admin/blocked-dates/{blockedDate}/delete', [AdminController::class, 'destroyBlockedDate'])->name('admin.blocked_dates.destroy');

// Servicios
Route::get('/admin/services', [AdminController::class, 'services'])->name('admin.services.index');
Route::post('/admin/service', [AdminController::class, 'storeService'])->name('admin.service.store');
Route::post('/admin/service/{service}/update', [AdminController::class, 'updateService'])->name('admin.service.update');
Route::post('/admin/service/{service}/delete', [AdminController::class, 'destroyService'])->name('admin.service.destroy');

// Membresías (planes)
Route::get('/admin/memberships', [AdminController::class, 'memberships'])->name('admin.memberships.index');
Route::post('/admin/membership', [AdminController::class, 'storeMembership'])->name('admin.membership.store');
Route::post('/admin/membership/{membership}/update', [AdminController::class, 'updateMembership'])->name('admin.membership.update');
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
Route::post('/admin/finance/pay-appointment/{appointment}', [FinanceController::class, 'payAppointment'])->name('admin.finance.pay');
Route::get('/admin/finance/payments', [FinanceController::class, 'payments'])->name('admin.finance.payments');

// ─── Reportes ────────────────────────────────────────────────────────────────
Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports.index');

// ─── API REST ────────────────────────────────────────────────────────────────
Route::prefix('api')->group(function () {
    Route::get('/clients', [ClientController::class, 'index']);
    Route::get('/finance/summary', [FinanceController::class, 'index']);
    Route::get('/finance/payments', [FinanceController::class, 'payments']);
});
