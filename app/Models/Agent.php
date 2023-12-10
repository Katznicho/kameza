<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'agent_id',
        'email',
        'phone_number',
        'pin',
        'password',
    ];

    //an agent registers many customers
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

}
