<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSubscription extends Model
{
    protected $table = 'customer_subscription';
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'subscription_plan_id',
        'phone_number',
        'number_of_children',
        'amount',
        'is_amount_paid',
        'is_active',
        'expires_at',
    ];

    //a customer subscription belongs to a customer
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    //a customer subscription belongs to a subscription plan
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id', 'id');
    }
}
