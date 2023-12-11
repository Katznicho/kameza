<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UssdSesion extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'session_id',
        'last_user_code',
        'text',
        'network_code',
        'service_code',
    ];

    //session belongs to customer
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'phone_number', 'phone_number');
    }
}
