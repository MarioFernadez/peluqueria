<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'birthdate',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'is_active' => 'boolean',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function clientMemberships()
    {
        return $this->hasMany(ClientMembership::class);
    }

    public function activeMembership()
    {
        return $this->hasOne(ClientMembership::class)
            ->where('end_date', '>=', now()->toDateString())
            ->latest();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
