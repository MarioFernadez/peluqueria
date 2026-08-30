<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Models\Appointment;
use Carbon\Carbon;

class FinanceController extends Controller
{
    private function checkAuth()
    {
        if (!Auth::check()) abort(403);
    }

    public function index(Request $request)
    {
        $this->checkAuth();

        $period = $request->get('period', 'month');

        [$from, $to] = $this->getPeriodDates($period, $request);

        // Totales generales del período
        $totalRevenue = Payment::whereBetween('paid_at', [$from, $to])->sum('amount');

        // Por método de pago
        $byMethod = Payment::whereBetween('paid_at', [$from, $to])
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('method')
            ->get();

        // Por tipo (cita vs membresía)
        $byType = Payment::whereBetween('paid_at', [$from, $to])
            ->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        // Ingresos diarios del período para gráfico
        $dailyRevenue = Payment::whereBetween('paid_at', [$from, $to])
            ->selectRaw('DATE(paid_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $now = now();
        $currentDate = $now->toDateString();
        $currentTime = $now->toTimeString();

        $pendingQuery = Appointment::where('payment_status', 'pendiente')
            ->whereNotIn('status', ['Cancelado', 'Cancelada'])
            ->where(function ($q) use ($currentDate, $currentTime) {
                $q->where('appointment_date', '<', $currentDate)
                  ->orWhere(function ($q2) use ($currentDate, $currentTime) {
                      $q2->where('appointment_date', $currentDate)
                         ->where('appointment_time', '<=', $currentTime);
                  });
            });

        // Pagos pendientes (citas sin pagar y que ya pasaron)
        $pendingAppointments = (clone $pendingQuery)
            ->with(['client', 'barber', 'service'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->limit(10)
            ->get();

        $pendingTotal = (clone $pendingQuery)->sum('total_price');

        return view('admin.finance.index', compact(
            'totalRevenue', 'byMethod', 'byType',
            'dailyRevenue', 'pendingAppointments',
            'pendingTotal', 'period', 'from', 'to'
        ));
    }

    public function payments(Request $request)
    {
        $this->checkAuth();

        $query = Payment::with(['client', 'appointment.barber'])->latest('paid_at');

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('from')) {
            $query->whereDate('paid_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('paid_at', '<=', $request->to);
        }

        $payments = $query->paginate(30)->withQueryString();

        return view('admin.finance.payments', compact('payments'));
    }

    public function payAppointment(Request $request, Appointment $appointment)
    {
        $this->checkAuth();
        
        $request->validate([
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia,otro',
        ]);

        if ($appointment->payment_status === 'pagado') {
            return redirect()->back()->with('error', 'El turno ya se encuentra pagado.');
        }

        // Crear registro de pago
        Payment::create([
            'client_id'      => $appointment->client_id, // Puede ser null si el cliente no está registrado
            'appointment_id' => $appointment->id,
            'amount'         => $appointment->total_price,
            'type'           => 'cita',
            'method'         => $request->payment_method,
            'paid_at'        => now(),
        ]);

        // Actualizar turno
        $appointment->update([
            'payment_status' => 'pagado',
            'status'         => 'Completada',
        ]);

        return redirect()->back()->with('success', 'Pago registrado correctamente.');
    }

    private function getPeriodDates(string $period, Request $request): array
    {
        return match ($period) {
            'day'   => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'week'  => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'year'  => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'custom' => [
                Carbon::parse($request->from)->startOfDay(),
                Carbon::parse($request->to)->endOfDay(),
            ],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }
}
