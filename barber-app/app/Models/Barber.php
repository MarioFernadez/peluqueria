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
}
