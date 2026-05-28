<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'membership_id',
        'start_date',
        'end_date',
        'payment_date',
        'payment_status',
        'payment_method',
        'services_remaining',
        'notes',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'payment_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function membership()
    {
        return $this->belongsTo(Membership::class);
    }

    public function isExpired(): bool
    {
        return $this->end_date->isPast();
    }

    public function isActive(): bool
    {
        return !$this->isExpired() && $this->payment_status === 'pagado';
    }
}
