<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Service;
use App\Models\Appointment;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index()
    {
        return view('booking');
    }

    public function getData()
    {
        $barbers = Barber::where('is_active', true)->get();
        $services = Service::where('is_active', true)->get();
        $memberships = \App\Models\Membership::where('is_active', true)->get();
        
        return response()->json([
            'barbers' => $barbers,
            'services' => $services,
            'memberships' => $memberships,
            'business' => [
                'name' => 'Barbería Premium',
                'assistant_name' => 'Barbi',
            ]
        ]);
    }

    public function getAvailability(Request $request)
    {
        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
        ]);

        $barber = Barber::findOrFail($request->barber_id);
        $service = Service::findOrFail($request->service_id);
        $date = $request->date;

        // Si el barbero no tiene horarios configurados, usar por defecto 09:00 a 18:00
        $startTime = \Carbon\Carbon::parse($date . ' ' . ($barber->start_time ?? '09:00:00'));
        $endTime = \Carbon\Carbon::parse($date . ' ' . ($barber->end_time ?? '18:00:00'));

        $appointments = Appointment::where('barber_id', $barber->id)
            ->where('appointment_date', $date)
            ->whereIn('status', ['Pendiente', 'Completado'])
            ->get();

        $availableTimes = [];
        $currentTime = $startTime->copy();
        $duration = $service->duration_min;

        while ($currentTime->copy()->addMinutes($duration)->lte($endTime)) {
            $slotStart = $currentTime->copy();
            $slotEnd = $currentTime->copy()->addMinutes($duration);
            $isAvailable = true;

            foreach ($appointments as $appt) {
                $apptStart = \Carbon\Carbon::parse($date . ' ' . $appt->appointment_time);
                $apptEnd = $apptStart->copy()->addMinutes($appt->duration_min);

                // Comprobar superposición de horarios
                if ($slotStart->lt($apptEnd) && $slotEnd->gt($apptStart)) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $availableTimes[] = $slotStart->format('H:i');
            }

            // Incrementar de a 30 minutos
            $currentTime->addMinutes(30);
        }

        return response()->json(['availableTimes' => $availableTimes]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'barber_id' => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'total_price' => 'required|numeric',
            'duration_min' => 'required|integer',
        ]);

        $appointment = Appointment::create(array_merge($validated, [
            'status' => 'Pendiente',
        ]));

        return response()->json(['success' => true, 'appointment' => $appointment]);
    }
}
