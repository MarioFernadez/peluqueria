<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Membership;
use App\Models\ClientMembership;
use Carbon\Carbon;

class ReportController extends Controller
{
    private function checkAuth()
    {
        if (!Auth::check()) abort(403);
    }

    public function index(Request $request)
    {
        $this->checkAuth();

        $from = $request->filled('from') ? Carbon::parse($request->from) : Carbon::now()->startOfMonth();
        $to   = $request->filled('to')   ? Carbon::parse($request->to)   : Carbon::now()->endOfMonth();

        // Servicios más vendidos
        $topServices = Appointment::whereBetween('appointment_date', [$from, $to])
            ->selectRaw('service_id, COUNT(*) as total')
            ->groupBy('service_id')
            ->orderByDesc('total')
            ->with('service')
            ->limit(5)
            ->get();

        // Barberos con más citas y ganancias
        $barberStats = Barber::withCount(['appointments as total_appointments' => function ($q) use ($from, $to) {
                $q->whereBetween('appointment_date', [$from, $to]);
            }])
            ->withSum(['appointments as total_revenue' => function ($q) use ($from, $to) {
                $q->whereBetween('appointment_date', [$from, $to])
                  ->where('payment_status', 'pagado');
            }], 'total_price')
            ->orderByDesc('total_appointments')
            ->get();

        // Clientes frecuentes (más visitas)
        $topClients = Client::withCount(['appointments as total_visits' => function ($q) use ($from, $to) {
                $q->whereBetween('appointment_date', [$from, $to]);
            }])
            ->orderByDesc('total_visits')
            ->limit(10)
            ->get();

        // Clientes inactivos (sin citas en 60 días)
        $inactiveClients = Client::where('is_active', true)
            ->whereDoesntHave('appointments', function ($q) {
                $q->where('appointment_date', '>=', now()->subDays(60));
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        // Membresías activas por plan
        $membershipStats = Membership::withCount(['clientMemberships as active_count' => function ($q) {
                $q->where('end_date', '>=', now()->toDateString())
                  ->where('payment_status', 'pagado');
            }])
            ->get();

        // Vencimientos próximos (7 días)
        $expiringSoon = ClientMembership::with(['client', 'membership'])
            ->where('end_date', '>=', now()->toDateString())
            ->where('end_date', '<=', now()->addDays(7)->toDateString())
            ->where('payment_status', 'pagado')
            ->orderBy('end_date')
            ->get();

        return view('admin.reports.index', compact(
            'topServices', 'barberStats', 'topClients',
            'inactiveClients', 'membershipStats', 'expiringSoon',
            'from', 'to'
        ));
    }
}
