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
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];
}
