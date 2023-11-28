<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait CustomerTrait
{
    public function checkPin(string $pin, string $phoneNumber)
    {
        //check user pin
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        $hashedPin = $getUser->pin;
        $pin = str_replace(" ", "", $pin);
        if (Hash::check($pin, $hashedPin)) {
            return true;
        } else {
            return false;
        }
    }

    public function updatePin(string $pin, string $phoneNumber)
    {
        //remove any spaces
        $pin = str_replace(" ", "", $pin);
        //print_r($pin);
        //update user pin
        $hashedPin = Hash::make($pin);
        //print_r($hashedPin);
        DB::table('users')->where('phone_number', $phoneNumber)->update(['pin' => $hashedPin]);
        return true;
    }


    public function getAccountBalance(string $phoneNumber)
    {
        //get user account balance
        $getUser = DB::table('users')->where('phone_number', $phoneNumber)->first();
        return  "UGX " . "" . $getUser->account;
    }

    public function checkIfCustomerExists(string $phoneNumber)
    {
        try {
            //check if user exists
            $getUser = DB::table('customers')->where('phone_number', $phoneNumber)->first();
            if ($getUser) {
                return true;
            } else {
                //create a new customer
                DB::table('customers')->insert([
                    'phone_number' => $phoneNumber,
                    "subscription_plan_id" => 1
                ]);
                return false;
            }
        } catch (\Throwable $th) {
            //throw $th;

            return false;
        }
    }

    //get user details
    public function getUserDetails(string $phoneNumber)
    {
        return DB::table('users')->where('phone_number', $phoneNumber)->first();
    }





    // function to update dynamicall different customer fields
    public function updateCustomerField(string $phoneNumber, string $field, string $value)
    {
        try {
            //code...
            DB::table('customers')->where('phone_number', $phoneNumber)->update([$field => $value]);
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }
    }

    public function getTotalAmountToPay(string $phoneNumber)
    {
        $getUser = DB::table('customers')->where('phone_number', $phoneNumber)->first();
        $plan_id  = $getUser->subscription_plan_id;
        $number_of_children = $getUser->number_of_children;
        $plan = DB::table('subscription_plans')->where('id', $plan_id)->first();
        return intval($plan->price) + intval($plan->additional_info_amount) * intval($number_of_children);
    }

    public function createUserAccount(string $phoneNumber)
    {
        $getUser = DB::table('customers')->where('phone_number', $phoneNumber)->first();
        try {
            //code...
            DB::table('accounts')->insert([
                'phone_number' => $phoneNumber,
                'account' => $this->getTotalAmountToPay($phoneNumber),
                'customer_id' => $getUser->id
            ]);
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }
    }


    public function createTransaction(string $phoneNumber, string $amount, string $description)
    {
        $getUser = DB::table('customers')->where('phone_number', $phoneNumber)->first();
        //create a  transaction
        DB::table('transactions')->insert([
            'phone_number' => $phoneNumber,
            'amount' => $amount,
            'type' => 'credit',
            'status' => 'completed',
            'description' => $description,
            'customer_id' => $getUser->id,
            'reference' => Str::uuid()
        ]);
        return true;
    }
}
