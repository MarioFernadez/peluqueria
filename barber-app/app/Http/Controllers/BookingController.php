<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Service;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $galleryWorks = \App\Models\GalleryWork::where('is_active', true)->orderBy('order')->get();
        $barbers = Barber::where('is_active', true)->get();
        $services = Service::where('is_active', true)->get();
        return view('welcome', compact('settings', 'galleryWorks', 'barbers', 'services'));
    }

    public function booking()
    {
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $waNumber = $settings['whatsapp_number'] ?? '595000000000';
        $businessName = $settings['hero_title'] ?? 'Athenea Barber';
        return view('booking', compact('waNumber', 'businessName'));
    }

    public function getData()
    {
        $barbers = Barber::where('is_active', true)->get();
        $services = Service::where('is_active', true)->get();
        $memberships = \App\Models\Membership::where('is_active', true)->get();

        return response()->json([
            'barbers'     => $barbers,
            'services'    => $services,
            'memberships' => $memberships,
            'business'    => ['name' => 'Athenea Barber', 'assistant_name' => 'Barbi'],
        ]);
    }

    public function getAvailability(Request $request)
    {
        $request->validate([
            'barber_id'  => 'required|exists:barbers,id',
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date',
        ]);

        $barber  = Barber::findOrFail($request->barber_id);
        $service = Service::findOrFail($request->service_id);
        $date    = $request->date;

        $startTime = \Carbon\Carbon::parse($date . ' 07:00:00');
        $endTime   = \Carbon\Carbon::parse($date . ' 22:00:00');
        $duration  = $service->duration_min;

        $appointments = Appointment::where('barber_id', $request->barber_id)
            ->where('appointment_date', $date)
            ->whereNotIn('status', ['Cancelado', 'Cancelada'])
            ->get();

        $now = now();
        $isToday = $now->toDateString() === $date;

        // Pre-calculate day name in Spanish (outside the loop for performance)
        $dayOfWeekEng = strtolower(\Carbon\Carbon::parse($date)->englishDayOfWeek);
        $dayOfWeekEsp = match ($dayOfWeekEng) {
            'monday'    => 'lunes',
            'tuesday'   => 'martes',
            'wednesday' => 'miercoles',
            'thursday'  => 'jueves',
            'friday'    => 'viernes',
            'saturday'  => 'sabado',
            'sunday'    => 'domingo',
            default     => '',
        };
        $complexSchedule = $barber->complex_schedule ?? null;

        $allSlots = [];
        $currentTime = $startTime->copy();

        while ($currentTime->copy()->addMinutes($duration)->lte($endTime)) {
            $slotStart = $currentTime->copy();
            $slotEnd   = $currentTime->copy()->addMinutes($duration);

            $isAvailable      = true;
            $isBlockedByAdmin = false;
            $isOutOfSchedule  = false;
            $isPast           = false;
            $bookedInfo       = null;

            // 1. Block hours that have already passed (today only)
            if ($isToday && $slotStart->lte($now)) {
                $isAvailable = false;
                $isPast      = true;
            }

            // 2. Schedule check
            if ($isAvailable) {
                if (is_array($complexSchedule) && isset($complexSchedule[$dayOfWeekEsp])) {
                    $dayData = $complexSchedule[$dayOfWeekEsp];
                    if ($dayData === false || $dayData === null) {
                        $isAvailable     = false;
                        $isOutOfSchedule = true; // day off
                    } elseif (is_array($dayData)) {
                        $slotTimeStr = $slotStart->format('H:i');
                        if (!isset($dayData[$slotTimeStr]) || !$dayData[$slotTimeStr]) {
                            $isAvailable     = false;
                            $isOutOfSchedule = true;
                        }
                    }
                } elseif (is_array($complexSchedule) && !empty($complexSchedule)) {
                    $isAvailable     = false;
                    $isOutOfSchedule = true; // day not in schedule
                } else {
                    // Fallback: start_time / end_time / lunch
                    if ($barber->start_time && $barber->end_time) {
                        $barberStart = \Carbon\Carbon::parse($date . ' ' . $barber->start_time);
                        $barberEnd   = \Carbon\Carbon::parse($date . ' ' . $barber->end_time);
                        if ($slotStart->lt($barberStart) || $slotEnd->gt($barberEnd)) {
                            $isAvailable     = false;
                            $isOutOfSchedule = true;
                        }
                    }
                    if ($barber->lunch_start_time && $barber->lunch_end_time) {
                        $lunchStart = \Carbon\Carbon::parse($date . ' ' . $barber->lunch_start_time);
                        $lunchEnd   = \Carbon\Carbon::parse($date . ' ' . $barber->lunch_end_time);
                        if ($slotStart->lt($lunchEnd) && $slotEnd->gt($lunchStart)) {
                            $isAvailable     = false;
                            $isOutOfSchedule = true;
                        }
                    }
                }
            }

            // 3. Check existing appointments
            if ($isAvailable) {
                foreach ($appointments as $appt) {
                    $apptStart = \Carbon\Carbon::parse($date . ' ' . $appt->appointment_time);
                    $apptEnd   = $apptStart->copy()->addMinutes($appt->duration_min);
                    if ($slotStart->lt($apptEnd) && $slotEnd->gt($apptStart)) {
                        $isAvailable = false;
                        if ($appt->status === 'Bloqueado') {
                            $isBlockedByAdmin = true;
                        } else {
                            $name   = $appt->customer_name ?? '';
                            $masked = mb_substr($name, 0, 1) . str_repeat('*', max(0, mb_strlen($name) - 1));
                            $bookedInfo = ['appointment_id' => $appt->id, 'customer_masked' => $masked];
                        }
                        break;
                    }
                }
            }

            // 4. Admin-blocked weekly slots
            if ($isAvailable || $isBlockedByAdmin) {
                $blockedWeekly = $barber->blocked_weekly_slots ?? [];
                if (isset($blockedWeekly[$dayOfWeekEng]) && in_array($slotStart->format('H:i'), $blockedWeekly[$dayOfWeekEng])) {
                    $isAvailable      = false;
                    $isBlockedByAdmin = true;
                }
            }

            $allSlots[] = [
                'time'                => $slotStart->format('H:i'),
                'available'           => $isAvailable,
                'booked'              => $bookedInfo,
                'is_blocked_by_admin' => $isBlockedByAdmin,
                'is_out_of_schedule'  => $isOutOfSchedule || $isPast,
            ];

            $currentTime->addMinutes(30);
        }

        $availableTimes = collect($allSlots)
            ->where('available', true)
            ->pluck('time')
            ->values();

        return response()->json([
            'availableTimes' => $availableTimes,
            'allSlots'       => $allSlots,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'nullable|string|max:50',
            'barber_id'        => 'required|exists:barbers,id',
            'service_id'       => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'total_price'      => 'required|numeric',
            'duration_min'     => 'required|integer',
        ]);

        $appointment = Appointment::create(array_merge($validated, [
            'status' => 'Pendiente',
        ]));

        return response()->json(['success' => true, 'appointment' => $appointment]);
    }

    /**
     * Cancela un turno. Solo funciona si está Pendiente o Confirmado.
     * El frontend verifica ownership via localStorage (appointment_id guardado al reservar).
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);

        if (!in_array($appointment->status, ['Pendiente', 'Confirmada'])) {
            return response()->json([
                'success' => false,
                'message' => 'El turno no puede cancelarse (ya fue completado o cancelado).',
            ], 422);
        }

        $appointment->update(['status' => 'Cancelado']);

        return response()->json(['success' => true, 'message' => 'Turno cancelado con exito.']);
    }

    public function notifyAdmin(Request $request)
    {
        $barberName = collect($request->barberName)->first();
        $serviceName = collect($request->serviceName)->first();
        $customerName = collect($request->customerName)->first();
        $time = collect($request->time)->first();
        
        $title = "¡Nuevo Turno Reservado! 📅";
        $body = "{$customerName} reservó {$serviceName} con {$barberName} a las {$time}.";

        $admins = \App\Models\User::all();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewAppointmentNotification($title, $body));
        }

        return response()->json(['success' => true]);
    }

    public function blockSlot(Request $request)
    {
        // This is now legacy, using toggleWeeklySlot instead
        return response()->json(['success' => false, 'message' => 'Use toggleWeeklySlot']);
    }

    public function unblockSlot(Request $request)
    {
        // This is now legacy, using toggleWeeklySlot instead
        return response()->json(['success' => false, 'message' => 'Use toggleWeeklySlot']);
    }

    public function getWeeklyAvailability(Request $request)
    {
        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'day_of_week' => 'required|string', // e.g., 'monday', 'tuesday'
        ]);

        $barber = Barber::findOrFail($request->barber_id);
        
        $startTime = \Carbon\Carbon::parse('07:00:00');
        $endTime   = \Carbon\Carbon::parse('22:00:00');
        $duration  = 30; // Standard 30min blocks for UI

        $blockedSlots = $barber->blocked_weekly_slots ?? [];
        $blockedForDay = $blockedSlots[$request->day_of_week] ?? [];

        $allSlots = [];
        $currentTime = $startTime->copy();

        while ($currentTime->copy()->addMinutes($duration)->lte($endTime)) {
            $slotStart = $currentTime->copy();
            $slotEnd   = $currentTime->copy()->addMinutes($duration);
            $timeStr = $slotStart->format('H:i');
            
            $isAvailable = true;
            $isBlockedByAdmin = in_array($timeStr, $blockedForDay);

            if ($isBlockedByAdmin) {
                $isAvailable = false;
            }

            $allSlots[] = [
                'time'      => $timeStr,
                'available' => $isAvailable,
                'booked'    => null,
                'is_blocked_by_admin' => $isBlockedByAdmin
            ];

            $currentTime->addMinutes($duration);
        }

        return response()->json([
            'allSlots' => $allSlots,
        ]);
    }

    public function toggleWeeklySlot(Request $request)
    {
        if (!Auth::check()) return abort(403);
        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'day_of_week' => 'required|string',
            'time' => 'required'
        ]);

        $barber = Barber::findOrFail($request->barber_id);
        $blockedSlots = $barber->blocked_weekly_slots ?? [];
        
        if (!isset($blockedSlots[$request->day_of_week])) {
            $blockedSlots[$request->day_of_week] = [];
        }

        $idx = array_search($request->time, $blockedSlots[$request->day_of_week]);
        if ($idx !== false) {
            // It's blocked, so unblock it
            unset($blockedSlots[$request->day_of_week][$idx]);
            // Re-index array to avoid object conversion in JSON
            $blockedSlots[$request->day_of_week] = array_values($blockedSlots[$request->day_of_week]);
        } else {
            // Not blocked, block it
            $blockedSlots[$request->day_of_week][] = $request->time;
        }

        $barber->blocked_weekly_slots = $blockedSlots;
        $barber->save();

        return response()->json(['success' => true, 'blocked_slots' => $blockedSlots]);
    }
}
