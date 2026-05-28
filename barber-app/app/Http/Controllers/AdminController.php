<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Membership;
use App\Models\User;
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

        $appointments = Appointment::with(['barber', 'service'])->orderBy('appointment_date', 'desc')->orderBy('appointment_time', 'desc')->get();
        $barbers = Barber::all();
        $services = Service::all();
        $memberships = Membership::all();

        $totalCaja = $appointments->sum('total_price');
        $totalTurnos = $appointments->count();

        return view('admin.dashboard', compact('appointments', 'barbers', 'services', 'memberships', 'totalCaja', 'totalTurnos'));
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
