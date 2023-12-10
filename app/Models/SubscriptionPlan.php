<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
    ];

    //a plan belongs to many customers


    // Define a relationship with subscription plans
    
    // Define a relationship with customers
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_subscription')->withTimestamps();
    }
}
