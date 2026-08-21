<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class BarberDashboardController extends Controller
{
    public function dashboard()
    {
        if (!Auth::check() || Auth::user()->role !== 'barber') {
            return redirect()->route('admin.login');
        }

        $barber = Auth::user()->barber;
        if (!$barber) {
            return redirect()->route('admin.login')->withErrors(['email' => 'No tienes un perfil de barbero asignado.']);
        }

        $today = now()->toDateString();
        $appointments = Appointment::with(['service', 'client'])
            ->where('barber_id', $barber->id)
            ->whereDate('appointment_date', '>=', $today)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        $schedule = $barber->complex_schedule ?? [];

        return view('barber.dashboard', compact('barber', 'appointments', 'schedule'));
    }

    public function updateSchedule(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'barber') {
            return abort(403);
        }

        $barber = Auth::user()->barber;
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
            if (!isset($scheduleData[$day])) {
                continue;
            }
            // Day off
            if ($scheduleData[$day] === 'off') {
                $cleanSchedule[$day] = false;
                continue;
            }
            // Hour blocks
            if (is_array($scheduleData[$day])) {
                $dayHours = [];
                foreach ($hours as $h) {
                    $dayHours[$h] = isset($scheduleData[$day][$h]) && $scheduleData[$day][$h] === '1';
                }
                $cleanSchedule[$day] = $dayHours;
            }
        }
        
        $barber->update(['complex_schedule' => $cleanSchedule]);
        
        return back()->with('success', 'Horarios guardados correctamente');
    }
}
