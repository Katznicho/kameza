<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'subscription_plan_id',
        'phone_number',
        'amount',
        'payment_mode',
        'payment_phone_number',
        'type',
        'status',
        'description',
        'reference',
        'network_code',
    ];

    //a transaction belongs to a customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // a transaction belongs to a subscription plan
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }
}
