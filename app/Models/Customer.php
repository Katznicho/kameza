<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
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

    //a customer belongs to an agent
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    

    public function sessions(): HasMany
    {
        return $this->hasMany(UssdSesion::class, 'phone_number', 'phone_number');
    }
    public function subscriptionPlans()
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'customer_subscription')->withTimestamps();
    }

    // a customer has many transactions
    public function transactions()
    {
        return $this->hasMany(Transactions::class);
    }

    public function totalSubscriptionPlans()
    {
        return $this->subscriptionPlans()->count();
    }
}
