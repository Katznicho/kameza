<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'linkId',
        'text',
        'to',
        'message_id',
        'date',
        'from',
        'status',
        'message',
    ];

    //a session message belongs to a customer from in the message table and in the customer table its the phone number
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'from', 'phone_number');
    }
}
