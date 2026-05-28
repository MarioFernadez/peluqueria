<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'visits',
        'included_services',
        'benefits',
        'is_active',
    ];

    protected $casts = [
        'included_services' => 'array',
        'is_active'         => 'boolean',
        'price'             => 'decimal:2',
    ];

    public function clientMemberships()
    {
        return $this->hasMany(ClientMembership::class);
    }

    public function activeClients()
    {
        return $this->clientMemberships()
            ->where('end_date', '>=', now()->toDateString())
            ->where('payment_status', 'pagado');
    }
}
