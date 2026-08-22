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
use Illuminate\Support\Str;

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
            if (Auth::user()->role === 'barber') {
                return redirect()->route('barber.dashboard');
            }
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

        // Generar agenda de 9 a 20 hrs
        $agenda = [];
        for ($i = 9; $i <= 20; $i++) {
            $timePrefix = sprintf('%02d:', $i);
            $appts = $todayAppointments->filter(function($a) use ($timePrefix) {
                return str_starts_with($a->appointment_time, $timePrefix);
            });
            $agenda[] = [
                'time' => sprintf('%02d:00', $i),
                'appointments' => $appts
            ];
        }

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
            'todayAppointments', 'agenda', 'todayRevenue', 'todayCount',
            'monthRevenue', 'newClientsMonth', 'activeMemberships',
            'expiringSoon', 'topBarber', 'topServices',
            'barbers', 'services', 'memberships',
            'totalTurnos', 'totalCaja'
        ));
    }

    public function pushSubscribe(Request $request)
    {
        $endpoint = $request->endpoint;
        $token = $request->keys['auth'];
        $key = $request->keys['p256dh'];

        $user = Auth::user();
        $user->updatePushSubscription($endpoint, $key, $token);

        return response()->json(['success' => true]);
    }

    public function barbers()
    {
        if (!Auth::check()) return redirect()->route('admin.login');
        $barbers = Barber::all();
        return view('admin.barbers.index', compact('barbers'));
    }

    public function barberSchedule(Barber $barber)
    {
        if (!Auth::check()) return redirect()->route('admin.login');
        $schedule = $barber->complex_schedule ?? [];
        return view('admin.barbers.schedule', compact('barber', 'schedule'));
    }

    public function updateBarberSchedule(Request $request, Barber $barber)
    {
        if (!Auth::check()) return abort(403);
        $scheduleData = $request->input('schedule', []);
        $days = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
        $hours = [
            '07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30',
            '11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30',
            '15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30',
            '19:00','19:30','20:00','20:30','21:00','21:30'
        ];
        $cleanSchedule = [];
        foreach ($days as $day) {
            if (!isset($scheduleData[$day])) continue;
            if ($scheduleData[$day] === 'off') {
                $cleanSchedule[$day] = false;
                continue;
            }
            if (is_array($scheduleData[$day])) {
                $dayHours = [];
                foreach ($hours as $h) {
                    // Replace ':' with '_' because PHP converts dots/spaces to underscores in form keys, 
                    // but ':' is usually fine. Just to be safe we check the exact string $h
                    $dayHours[$h] = isset($scheduleData[$day][$h]) && $scheduleData[$day][$h] === '1';
                }
                $cleanSchedule[$day] = $dayHours;
            }
        }
        $barber->update(['complex_schedule' => $cleanSchedule]);
        return back()->with('success', 'Horarios de ' . $barber->name . ' actualizados correctamente');
    }

    public function services()
    {
        if (!Auth::check()) return redirect()->route('admin.login');
        $services = Service::orderBy('category')->get();
        return view('admin.services.index', compact('services'));
    }

    public function memberships()
    {
        if (!Auth::check()) return redirect()->route('admin.login');
        $memberships = Membership::all();
        return view('admin.memberships.index', compact('memberships'));
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
            'email' => 'required|email|unique:users,email',
            'image' => 'nullable|image|max:2048',
            'specialties' => 'nullable|string',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'lunch_start_time' => 'nullable',
            'lunch_end_time' => 'nullable'
        ]);
        if(!empty($validated['specialties'])) {
            $validated['specialties'] = array_map('trim', explode(',', $validated['specialties']));
        }
        $validated['is_active'] = $request->boolean('is_active');
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/images/barbers');
            $validated['image_path'] = str_replace('public/', 'storage/', $path);
        }
        
        $password = Str::random(8);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'role' => 'barber',
        ]);
        
        $validated['user_id'] = $user->id;
        $barber = Barber::create($validated);
        
        $user->update(['barber_id' => $barber->id]);
        
        return back()->with('success', "Barbero creado correctamente. El acceso del barbero es: Email: {$validated['email']} / Contraseña: {$password}");
    }

    public function updateBarber(Request $request, Barber $barber)
    {
        if (!Auth::check()) return abort(403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'specialties' => 'nullable|string',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'lunch_start_time' => 'nullable',
            'lunch_end_time' => 'nullable'
        ]);
        if(!empty($validated['specialties'])) {
            $validated['specialties'] = array_map('trim', explode(',', $validated['specialties']));
        } else {
            $validated['specialties'] = [];
        }
        $validated['is_active'] = $request->boolean('is_active');
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/images/barbers');
            $validated['image_path'] = str_replace('public/', 'storage/', $path);
        }
        
        $barber->update($validated);
        return back()->with('success', 'Barbero actualizado');
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

    public function updateService(Request $request, Service $service)
    {
        if (!Auth::check()) return abort(403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric',
            'duration_min' => 'required|integer',
        ]);
        $validated['is_active'] = $request->has('is_active');
        $service->update($validated);
        return back()->with('success', 'Servicio actualizado');
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

    public function updateMembership(Request $request, Membership $membership)
    {
        if (!Auth::check()) return abort(403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'visits' => 'required|string',
            'benefits' => 'nullable|string',
        ]);
        $validated['is_active'] = $request->has('is_active');
        $membership->update($validated);
        return back()->with('success', 'Plan actualizado');
    }
}
