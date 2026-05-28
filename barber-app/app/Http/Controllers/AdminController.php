<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Membership;
use App\Models\User;
use App\Models\Client;
use App\Models\ClientMembership;
use App\Models\Payment;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Las credenciales no coinciden con nuestros registros.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $today = now()->toDateString();

        // Citas de hoy
        $todayAppointments = Appointment::with(['barber', 'service', 'client'])
            ->whereDate('appointment_date', $today)
            ->orderBy('appointment_time')
            ->get();

        // Métricas del día
        $todayRevenue = Payment::whereDate('paid_at', $today)->sum('amount');
        $todayCount   = $todayAppointments->count();

        // Métricas del mes
        $monthRevenue     = Payment::whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('amount');
        $newClientsMonth  = Client::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $activeMemberships = ClientMembership::where('end_date', '>=', $today)->where('payment_status', 'pagado')->count();

        // Próximos vencimientos de membresías (7 días)
        $expiringSoon = ClientMembership::with(['client', 'membership'])
            ->where('end_date', '>=', $today)
            ->where('end_date', '<=', now()->addDays(7)->toDateString())
            ->where('payment_status', 'pagado')
            ->orderBy('end_date')
            ->limit(5)
            ->get();

        // Barbero con más citas este mes
        $topBarber = Barber::withCount(['appointments as month_appointments' => function ($q) {
                $q->whereMonth('appointment_date', now()->month)->whereYear('appointment_date', now()->year);
            }])->orderByDesc('month_appointments')->first();

        // Servicios más vendidos (mes)
        $topServices = Service::withCount(['appointments as month_count' => function ($q) {
                $q->whereMonth('appointment_date', now()->month)->whereYear('appointment_date', now()->year);
            }])->orderByDesc('month_count')->limit(5)->get();

        // Datos para el panel de gestión
        $barbers     = Barber::all();
        $services    = Service::all();
        $memberships = Membership::all();
        $totalTurnos = Appointment::count();
        $totalCaja   = Payment::sum('amount');

        return view('admin.dashboard', compact(
            'todayAppointments', 'todayRevenue', 'todayCount',
            'monthRevenue', 'newClientsMonth', 'activeMemberships',
            'expiringSoon', 'topBarber', 'topServices',
            'barbers', 'services', 'memberships',
            'totalTurnos', 'totalCaja'
        ));
    }

    // Acciones básicas para turnos
    public function updateAppointmentStatus(Request $request, Appointment $appointment)
    {
        if (!Auth::check()) return abort(403);
        $appointment->update(['status' => $request->status]);
        return back();
    }

    // Barber CRUD
    public function storeBarber(Request $request)
    {
        if (!Auth::check()) return abort(403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialties' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        if(!empty($validated['specialties'])) {
            $validated['specialties'] = array_map('trim', explode(',', $validated['specialties']));
        }
        $validated['is_active'] = $request->has('is_active');
        Barber::create($validated);
        return back()->with('success', 'Barbero creado');
    }

    public function destroyBarber(Barber $barber)
    {
        if (!Auth::check()) return abort(403);
        $barber->delete();
        return back()->with('success', 'Barbero eliminado');
    }

    // Service CRUD
    public function storeService(Request $request)
    {
        if (!Auth::check()) return abort(403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'duration_min' => 'required|integer',
            'is_active' => 'boolean'
        ]);
        $validated['is_active'] = $request->has('is_active');
        Service::create($validated);
        return back()->with('success', 'Servicio creado');
    }

    public function destroyService(Service $service)
    {
        if (!Auth::check()) return abort(403);
        $service->delete();
        return back()->with('success', 'Servicio eliminado');
    }

    // Membership CRUD
    public function storeMembership(Request $request)
    {
        if (!Auth::check()) return abort(403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'visits' => 'required|string',
            'benefits' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        $validated['is_active'] = $request->has('is_active');
        Membership::create($validated);
        return back()->with('success', 'Plan creado');
    }

    public function destroyMembership(Membership $membership)
    {
        if (!Auth::check()) return abort(403);
        $membership->delete();
        return back()->with('success', 'Plan eliminado');
    }
}
