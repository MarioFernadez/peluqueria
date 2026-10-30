<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedDate extends Model
{
    protected $fillable = [
        'barber_id',
        'date',
        'reason',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }
}
