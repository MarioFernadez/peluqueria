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
        'appointment_date' => 'date',
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
}
