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
                    "policy" => Str::random(10),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                return false;
            }
        } catch (\Throwable $th) {
            //throw $th;

            return false;
        }
    }

    //check if use has an account
    public function checkIfCustomerHasAccount(string $phoneNumber)
    {
        $getUser = DB::table('customer_subscription')->where('phone_number', $phoneNumber)
            ->where("is_active", 1)
            ->first();
        if ($getUser) {
            return true;
        } else {
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
            // Update the specified field for the identified customer_subscription record

            DB::table('customer_subscription')
                ->where('phone_number', $phoneNumber)
                ->orderBy('id', 'desc')
                ->limit(1)
                ->update([$field => $value]);

            return true;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    //update customer table with tje  agent id and registration_type set to agent

    public function updateCustomerAgent(string $phoneNumber, string $agent_id)
    {

        DB::table('customers')
            ->where('phone_number', $phoneNumber)
            ->update(['agent_id' => $agent_id, 'registration_type' => 'agent']);
        return true;
    }

    //update the customer table with  nin , name and location

    public function updateCustomerDetails(string $phoneNumber, string $nin, string $name, string $location)
    {
        DB::table('customers')
            ->where('phone_number', $phoneNumber)
            ->update(['nin' => $nin, 'name' => $name, 'location' => $location]);
        return true;
    }


    public function checkIfAgentExists(string $agent_id)
    {

        $agent_id =  str_replace(" ", "", $agent_id);
        $getUser = DB::table('agents')->where('agent_id', $agent_id)->first();
        if ($getUser) {
            return true;
        } else {
            return false;
        }
    }

    //get agent details
    public function getAgentDetails(string $agent_id)
    {

        return DB::table('agents')->where('agent_id', $agent_id)->first();
    }


    public function getTotalAmountToPay(string $phoneNumber)
    {

        //get the latest subscription or the last created
        $getUser = DB::table('customer_subscription')->where('phone_number', $phoneNumber)->orderBy('id', 'desc')->first();
        $plan_id = $getUser->subscription_plan_id;
        $plan = DB::table('subscription_plans')->where('id', $plan_id)->first();
        return intval($plan->price) + intval($plan->additional_info_amount) * intval($getUser->number_of_children);
    }



    public function createUserAccount(string $phoneNumber, string $plan_id)
    {
        $getUser = DB::table('customers')->where('phone_number', $phoneNumber)->first();
        try {
            //code...
            DB::table('customer_subscription')->insert([
                'phone_number' => $phoneNumber,
                'customer_id' => $getUser->id,
                'subscription_plan_id' => $plan_id,
                //expires after one year
                'expires_at' => now()->addYear(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            return false;
        }
    }


    public function createTransaction(string $phoneNumber, string $amount, string $description, string $payment_phone_number)
    {
        try {
            $getUser = DB::table('customers')->where('phone_number', $phoneNumber)->first();
            $getUserSubscription = DB::table('customer_subscription')->where('phone_number', $phoneNumber)->orderBy('id', 'desc')->first();
            $plan_id = $getUserSubscription->subscription_plan_id;
            //create a  transaction
            DB::table('transactions')->insert([
                'phone_number' => $phoneNumber,
                'amount' => $amount,
                'type' => 'credit',
                'status' => 'completed',
                'description' => $description,
                'customer_id' => $getUser->id,
                'reference' => Str::uuid(),
                'subscription_plan_id' => $plan_id,
                'payment_mode' => 'ussd',
                'payment_phone_number' => $payment_phone_number,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            return $th->getMessage();
        }
    }

    public function validateNinNumber(string $nin)
    {
        // return (bool)preg_match('/^[A-Z]{2}[A-Z\d]{6}[A-Z]\d{2}[A-Z\d]{4}$/', $nin);

        //length of nin should be 10
        if (strlen($nin) == 14) {
            return true;
        } else {
            return false;
        }
    }

    //get customer details
    public function getCustomerDetails(string $phoneNumber)
    {
        return DB::table('customers')->where('phone_number', $phoneNumber)->first();
    }

    //get current custoer plan
    public function getCustomerPlans(string $phoneNumber)
    {
        //get the total subscription plans of the customer
        $total_plans = DB::table('customer_subscription')->where('phone_number', $phoneNumber)->get();
        return count($total_plans);
    }
    //get user account
    public function getUserAccount(string $phoneNumber)
    {
        return DB::table('customer_subscription')->where('phone_number', $phoneNumber)->first();
    }
}
