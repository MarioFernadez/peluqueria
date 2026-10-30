<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Service;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

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
        return view('booking', compact('waNumber', 'businessName', 'settings'));
    }

    public function getData()
    {
        $barbers = Barber::where('is_active', true)->get();
        $services = Service::where('is_active', true)->get();
        $memberships = \App\Models\Membership::where('is_active', true)->get();

        $businessName = \App\Models\Setting::where('key', 'hero_title')->value('value') ?? 'Athenea Barber';

        return response()->json([
            'barbers'     => $barbers,
            'services'    => $services,
            'memberships' => $memberships,
            'business'    => ['name' => $businessName, 'assistant_name' => 'Barbi'],
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

        // Check if the date is blocked globally or for this specific barber
        $blockedDate = \App\Models\BlockedDate::whereDate('date', $date)
            ->where(function ($query) use ($barber) {
                $query->whereNull('barber_id')
                      ->orWhere('barber_id', $barber->id);
            })->first();

        $startTime = Carbon::parse($date . ' 07:00:00');
        $endTime   = Carbon::parse($date . ' 22:00:00');
        $duration  = max(15, (int) ($service->duration_min ?: 30));

        $appointments = Appointment::where('barber_id', $request->barber_id)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['Cancelado', 'Cancelada', 'cancelado', 'cancelada'])
            ->get();

        $now = now();
        $isToday = $now->toDateString() === $date;

        // Pre-calculate day name in Spanish and English
        $dayOfWeekEng = strtolower(Carbon::parse($date)->englishDayOfWeek);
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
        $blockedWeekly = $barber->blocked_weekly_slots ?? [];
        $blockedForDay = $blockedWeekly[$dayOfWeekEng] ?? [];

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

            // 0. Check if the entire date is blocked
            if ($blockedDate) {
                $isAvailable = false;
                $isBlockedByAdmin = true;
            }

            // 1. Block hours that have already passed (today only)
            if ($isAvailable && $isToday && $slotStart->lte($now)) {
                $isAvailable = false;
                $isPast      = true;
            }

            // 2. Schedule check across entire service duration
            if ($isAvailable) {
                if (is_array($complexSchedule) && isset($complexSchedule[$dayOfWeekEsp])) {
                    $dayData = $complexSchedule[$dayOfWeekEsp];
                    if ($dayData === false || $dayData === null) {
                        $isAvailable     = false;
                        $isOutOfSchedule = true; // day off
                    } elseif (is_array($dayData)) {
                        $chunk = $slotStart->copy();
                        while ($chunk->lt($slotEnd)) {
                            $chunkTimeStr = $chunk->format('H:i');
                            if (!isset($dayData[$chunkTimeStr]) || !$dayData[$chunkTimeStr]) {
                                $isAvailable     = false;
                                $isOutOfSchedule = true;
                                break;
                            }
                            $chunk->addMinutes(30);
                        }
                    }
                } elseif (is_array($complexSchedule) && !empty($complexSchedule)) {
                    $isAvailable     = false;
                    $isOutOfSchedule = true; // day not in schedule
                } else {
                    // Fallback: start_time / end_time / lunch
                    if ($barber->start_time && $barber->end_time) {
                        $barberStart = Carbon::parse($date . ' ' . $barber->start_time);
                        $barberEnd   = Carbon::parse($date . ' ' . $barber->end_time);
                        if ($slotStart->lt($barberStart) || $slotEnd->gt($barberEnd)) {
                            $isAvailable     = false;
                            $isOutOfSchedule = true;
                        }
                    }
                    if ($barber->lunch_start_time && $barber->lunch_end_time) {
                        $lunchStart = Carbon::parse($date . ' ' . $barber->lunch_start_time);
                        $lunchEnd   = Carbon::parse($date . ' ' . $barber->lunch_end_time);
                        if ($slotStart->lt($lunchEnd) && $slotEnd->gt($lunchStart)) {
                            $isAvailable     = false;
                            $isOutOfSchedule = true;
                        }
                    }
                }
            }

            // 3. Admin-blocked weekly slots across entire service duration
            if ($isAvailable && is_array($blockedForDay) && !empty($blockedForDay)) {
                $chunk = $slotStart->copy();
                while ($chunk->lt($slotEnd)) {
                    $chunkTimeStr = $chunk->format('H:i');
                    if (in_array($chunkTimeStr, $blockedForDay)) {
                        $isAvailable      = false;
                        $isBlockedByAdmin = true;
                        break;
                    }
                    $chunk->addMinutes(30);
                }
            }

            // 4. Check existing overlapping appointments
            if ($isAvailable) {
                foreach ($appointments as $appt) {
                    $apptStart = Carbon::parse($date . ' ' . $appt->appointment_time);
                    $apptDuration = max(15, (int) ($appt->duration_min ?: 30));
                    $apptEnd   = $apptStart->copy()->addMinutes($apptDuration);

                    // Interval [slotStart, slotEnd) overlaps with [apptStart, apptEnd) if slotStart < apptEnd AND slotEnd > apptStart
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
            'appointment_date' => 'required|date_format:Y-m-d',
            'appointment_time' => 'required|string|max:10',
            'total_price'      => 'nullable|numeric',
            'duration_min'     => 'nullable|integer',
        ]);

        $barber = Barber::findOrFail($validated['barber_id']);
        $service = Service::findOrFail($validated['service_id']);

        if (!$barber->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'El barbero seleccionado no se encuentra disponible.',
            ], 422);
        }

        if (!$service->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'El servicio seleccionado no está disponible.',
            ], 422);
        }

        $date = $validated['appointment_date'];
        $time = $validated['appointment_time'];
        $duration = max(15, (int) ($service->duration_min ?: ($validated['duration_min'] ?? 30)));
        $totalPrice = $service->price ?? ($validated['total_price'] ?? 0);

        // 1. Validate barber availability according to their working schedule & blocked dates
        if (!$barber->isAvailableFor($date, $time, $duration)) {
            return response()->json([
                'success' => false,
                'message' => 'El horario seleccionado no se encuentra disponible en la agenda del barbero o ya ha pasado.',
            ], 422);
        }

        // 2. Concurrency-safe atomic lock to avoid simultaneous double-booking
        $lockKey = "booking_lock_barber_{$barber->id}_{$date}";
        $lock = null;
        try {
            $lock = Cache::lock($lockKey, 10);
            $lock->block(5);
        } catch (\Throwable $e) {
            $lock = null;
        }

        try {
            $appointment = DB::transaction(function () use ($validated, $barber, $service, $date, $time, $duration, $totalPrice) {
                // Strict check: verify no active overlapping appointment exists
                if (Appointment::hasConflict($barber->id, $date, $time, $duration)) {
                    return null;
                }

                return Appointment::create([
                    'customer_name'    => trim($validated['customer_name']),
                    'customer_phone'   => $validated['customer_phone'] ?? null,
                    'barber_id'        => $barber->id,
                    'service_id'       => $service->id,
                    'appointment_date' => $date,
                    'appointment_time' => $time,
                    'total_price'      => $totalPrice,
                    'duration_min'     => $duration,
                    'status'           => 'Pendiente',
                    'payment_method'   => 'efectivo',
                    'payment_status'   => 'pendiente',
                ]);
            });

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => '¡Lo sentimos! Este horario acaba de ser reservado por otro cliente. Por favor elegí otro horario disponible.',
                ], 409);
            }

            return response()->json([
                'success'     => true,
                'appointment' => $appointment,
            ]);
        } finally {
            if ($lock) {
                try {
                    $lock->release();
                } catch (\Throwable $e) {
                    // Ignore release errors
                }
            }
        }
    }

    /**
     * Cancela un turno. Solo funciona si está Pendiente o Confirmado
     * y si faltan más de 5 minutos para la hora del turno.
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

        // Verificar que falten más de 5 minutos para el turno
        $dateStr = Carbon::parse($appointment->appointment_date)->format('Y-m-d');
        $appointmentDatetime = Carbon::parse(
            $dateStr . ' ' . $appointment->appointment_time
        );
        $minutesUntilAppointment = now()->diffInMinutes($appointmentDatetime, false);

        if ($minutesUntilAppointment < 5) {
            return response()->json([
                'success' => false,
                'message' => 'No es posible cancelar el turno: debe hacerse con al menos 5 minutos de anticipación.',
            ], 422);
        }

        $appointment->update(['status' => 'Cancelado']);

        return response()->json(['success' => true, 'message' => 'Turno cancelado correctamente.']);
    }

    public function notifyAdmin(Request $request)
    {
        $barberName = $request->barberName ?? $request->barber ?? 'un barbero';
        if (is_array($barberName)) $barberName = reset($barberName);

        $serviceName = $request->serviceName ?? $request->service ?? 'un servicio';
        if (is_array($serviceName)) $serviceName = reset($serviceName);

        $customerName = $request->customerName ?? $request->customer_name ?? 'un cliente';
        if (is_array($customerName)) $customerName = reset($customerName);

        $time = $request->time ?? '';
        if (is_array($time)) $time = reset($time);
        
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

    /**
     * Returns the list of blocked dates for the next N days for a given barber.
     * Includes both global blocks (barber_id = null) and barber-specific blocks.
     */
    public function getBlockedDates(Request $request)
    {
        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
        ]);

        $days = (int) ($request->input('days', 60));
        $from = now()->toDateString();
        $to   = now()->addDays($days)->toDateString();

        $blocked = \App\Models\BlockedDate::where(function ($query) use ($request) {
                $query->whereNull('barber_id')
                      ->orWhere('barber_id', $request->barber_id);
            })
            ->whereBetween('date', [$from, $to])
            ->get()
            ->map(fn($b) => [
                'date'   => $b->date->toDateString(),
                'reason' => $b->reason,
            ]);

        return response()->json(['blocked_dates' => $blocked]);
    }
}
