<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_path',
        'user_id',
        'complex_schedule',
        'specialties',
        'working_days',
        'start_time',
        'end_time',
        'lunch_start_time',
        'lunch_end_time',
        'blocked_weekly_slots',
        'is_active',
    ];

    protected $casts = [
        'specialties' => 'array',
        'working_days' => 'array',
        'complex_schedule' => 'array',
        'blocked_weekly_slots' => 'array',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules()
    {
        return $this->hasMany(BarberSchedule::class);
    }

    public function totalRevenue(): float
    {
        return (float) $this->appointments()
            ->where('payment_status', 'pagado')
            ->sum('total_price');
    }

    /**
     * Checks if the barber is working and available on a given date/time for a duration.
     * Validates working hours, complex schedules, blocked dates, and weekly blocks.
     */
    public function isAvailableFor(string $date, string $startTime, int $durationMin): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // 1. Check if date is blocked globally or for this barber
        $isBlocked = BlockedDate::whereDate('date', $date)
            ->where(function ($query) {
                $query->whereNull('barber_id')
                      ->orWhere('barber_id', $this->id);
            })->exists();

        if ($isBlocked) {
            return false;
        }

        $start = \Carbon\Carbon::parse($date . ' ' . $startTime);
        $duration = max(15, $durationMin);
        $end = $start->copy()->addMinutes($duration);

        // 2. Cannot book in the past
        if ($start->lte(now())) {
            return false;
        }

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

        $complexSchedule = $this->complex_schedule ?? null;

        // 3. Check schedule
        if (is_array($complexSchedule) && isset($complexSchedule[$dayOfWeekEsp])) {
            $dayData = $complexSchedule[$dayOfWeekEsp];
            if ($dayData === false || $dayData === null) {
                return false; // Day off
            }

            if (is_array($dayData)) {
                $chunk = $start->copy();
                while ($chunk->lt($end)) {
                    $chunkTimeStr = $chunk->format('H:i');
                    if (!isset($dayData[$chunkTimeStr]) || !$dayData[$chunkTimeStr]) {
                        return false;
                    }
                    $chunk->addMinutes(30);
                }
            }
        } elseif (is_array($complexSchedule) && !empty($complexSchedule)) {
            return false; // Day not present in complex schedule
        } else {
            // Fallback: start_time / end_time / lunch
            if ($this->start_time && $this->end_time) {
                $barberStart = \Carbon\Carbon::parse($date . ' ' . $this->start_time);
                $barberEnd   = \Carbon\Carbon::parse($date . ' ' . $this->end_time);
                if ($start->lt($barberStart) || $end->gt($barberEnd)) {
                    return false;
                }
            }
            if ($this->lunch_start_time && $this->lunch_end_time) {
                $lunchStart = \Carbon\Carbon::parse($date . ' ' . $this->lunch_start_time);
                $lunchEnd   = \Carbon\Carbon::parse($date . ' ' . $this->lunch_end_time);
                if ($start->lt($lunchEnd) && $end->gt($lunchStart)) {
                    return false;
                }
            }
        }

        // 4. Admin-blocked weekly slots
        $blockedWeekly = $this->blocked_weekly_slots ?? [];
        $blockedForDay = $blockedWeekly[$dayOfWeekEng] ?? [];
        if (is_array($blockedForDay) && !empty($blockedForDay)) {
            $chunk = $start->copy();
            while ($chunk->lt($end)) {
                $chunkTimeStr = $chunk->format('H:i');
                if (in_array($chunkTimeStr, $blockedForDay)) {
                    return false;
                }
                $chunk->addMinutes(30);
            }
        }

        return true;
    }
}
