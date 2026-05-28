<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarberSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'barber_id',
        'date',
        'day_of_week',
        'start_time',
        'end_time',
        'is_blocked',
        'reason',
    ];

    protected $casts = [
        'date'       => 'date',
        'is_blocked' => 'boolean',
    ];

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }
}
