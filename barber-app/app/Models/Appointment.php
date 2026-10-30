<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'customer_name',
        'customer_phone',
        'barber_id',
        'service_id',
        'appointment_date',
        'appointment_time',
        'status',
        'payment_method',
        'payment_status',
        'total_price',
        'duration_min',
        'notes',
    ];

    protected $casts = [
        'appointment_date' => 'date:Y-m-d',
        'total_price'      => 'decimal:2',
    ];

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Check if an appointment interval overlaps with any non-cancelled appointment.
     */
    public static function hasConflict(int $barberId, string $date, string $startTime, int $durationMin, ?int $excludeAppointmentId = null): bool
    {
        return self::getConflictingAppointments($barberId, $date, $startTime, $durationMin, $excludeAppointmentId)->isNotEmpty();
    }

    /**
     * Get all active appointments that overlap with the given interval.
     */
    public static function getConflictingAppointments(int $barberId, string $date, string $startTime, int $durationMin, ?int $excludeAppointmentId = null)
    {
        $start = \Carbon\Carbon::parse($date . ' ' . $startTime);
        $duration = max(15, $durationMin);
        $end = $start->copy()->addMinutes($duration);

        $query = self::where('barber_id', $barberId)
            ->whereDate('appointment_date', $date)
            ->whereNotIn('status', ['Cancelado', 'Cancelada', 'cancelado', 'cancelada']);

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query->get()->filter(function ($appt) use ($date, $start, $end) {
            $apptStart = \Carbon\Carbon::parse($date . ' ' . $appt->appointment_time);
            $apptDuration = max(15, (int) ($appt->duration_min ?: 30));
            $apptEnd = $apptStart->copy()->addMinutes($apptDuration);

            // Interval [start, end) overlaps with [apptStart, apptEnd) if start < apptEnd AND end > apptStart
            return $start->lt($apptEnd) && $end->gt($apptStart);
        })->values();
    }

    /**
     * Helper to check if this specific model instance has a conflict with another appointment.
     */
    public function conflictsWithExisting(): bool
    {
        if (in_array(strtolower($this->status), ['cancelado', 'cancelada'])) {
            return false;
        }

        $date = $this->appointment_date instanceof \DateTimeInterface 
            ? $this->appointment_date->format('Y-m-d') 
            : (string) $this->appointment_date;

        return self::hasConflict(
            (int) $this->barber_id,
            $date,
            (string) $this->appointment_time,
            (int) ($this->duration_min ?: 30),
            $this->id
        );
    }
}
