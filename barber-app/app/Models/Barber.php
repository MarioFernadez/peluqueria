<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'specialties',
        'working_days',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'specialties' => 'array',
        'working_days' => 'array',
        'is_active' => 'boolean',
    ];

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
