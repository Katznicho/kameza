<?php

namespace App\Http\Controllers;


use App\Traits\CustomerTrait;
use App\Traits\MessageTrait;
use App\Traits\ResponseTrait;
use App\Traits\SessionTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UssdController extends Controller
{

    use ResponseTrait, SessionTrait, CustomerTrait, MessageTrait;


    private function welcomRegisteredCustomer(Request $request,  $details,  $plan, $account)
    {
        $nextRenewalDate = Carbon::parse($account->expires_at)->format('Y-m-d');


        $response = "Hello : " . $details->name . "\n  Policy Number :" . $details->policy . "\n ";
        $response .= "Your plan is : " . $plan->name . "\n Next renewal date is :  $nextRenewalDate  " . "\n";
        $response .= "1. Renew your plan" . "\n";
        $response .= "2. Change Plan" . "\n";
        $response .= "3. Contact Support" . "\n";
        $this->storeUserSession($request, "00");
        return $this->writeResponse($response, false);
    }

    public function processCustomerWithccount(Request $request)
    {
        try {

            //code...
            $details = $this->getCustomerDetails($request->phoneNumber);
            $plan = $this->getCustomerPlan($request->phoneNumber);
            $account = $this->getUserAccount($request->phoneNumber);
            if ($request->text == "") {
                return $this->welcomRegisteredCustomer($request, $details, $plan, $account);
            } else {
                $last_response = $this->getLastUserSession($request->phoneNumber);

                switch ($last_response->last_user_code) {
                    case '00':
                        if ($request->text == "1") {
                            $this->storeUserSession($request, "RenewPlan");
                            return $this->writeResponse("You will be contacted shortly", true);
                        } elseif ($request->text == "2") {
                            $this->storeUserSession($request, "ChangePlan");
                            return $this->writeResponse("Change Plan", true);
                        } elseif ($request->text == "3") {
                            return $this->writeResponse("You will be contacted shortly", true);
                        }
                        break;
                    case "RenewPlan":
                        return $this->welcomeUser($request);
                        break;
                    case "ChangePlan":
                        return $this->welcomeUser($request);
                        break;
                    case '2':
                        return $this->welcomeUserWithAccount($request, $details, $plan);
                        break;
                    case '3':
                        return $this->welcomeUserWithAccount($request, $details, $plan);
                        break;
                    default:
                        return $this->welcomeUserWithAccount($request, $details, $plan);
                        break;
                }
            }

            return $this->welcomeUserWithAccount($request, $details, $plan);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->writeResponse($th->getMessage(), true);
        }
    }

    public function process(Request $request)
    {
        if ($this->checkIfCustomerHasAccount($request->phoneNumber)) {
            return $this->processCustomerWithccount($request);
        } else {
            return $this->processCustomerWithOutAccount($request);
        }
    }

    public function processCustomerWithOutAccount(Request $request)
    {

        try {
            $this->checkIfCustomerExists($request->phoneNumber);
            if ($request->text == "") {
                return $this->welcomeUser($request);
            } else {
                $last_response =  $this->getLastUserSession($request->phoneNumber);
                switch ($last_response->last_user_code) {
                    case '00':
                        if ($request->text == "1") {
                            $this->updateCustomerField($request->phoneNumber, "subscription_plan_id", 1);
                            return $this->howManyChildren($request);
                        } elseif ($request->text == "2") {
                            $this->updateCustomerField($request->phoneNumber, "subscription_plan_id", 2);
                            return $this->howManyChildren($request);
                        } elseif ($request->text == "3") {
                            $this->updateCustomerField($request->phoneNumber, "subscription_plan_id", 3);
                            return $this->howManyChildren($request);
                        } elseif ($request->text == "4") {
                            return $this->writeResponse("You seleted help", true);
                        } else {
                            return $this->writeResponse("We did not understand your choice", true);
                        }
                    case "Children":
                        $children = $request->text;
                        $children_num =  explode("*", $children)[1];
                        //check if children is empty
                        if ($children_num == "") {
                            return $this->howManyChildren($request);
                        }
                        //check if its not a number
                        if (!is_numeric(intval($children_num))) {
                            return $this->howManyChildren($request);
                        }
                        //check if its greater than 3
                        if (intval($children_num) > 3) {
                            return $this->howManyChildren($request);
                        }
                        $this->updateCustomerField($request->phoneNumber, "number_of_children", $children_num);
                        //store user session
                        $this->storeUserSession($request, "Name");
                        return $this->writeResponse("Please enter your name", false);
                        break;
                        break;
                    case "Name":
                        $name = explode("*", "$request->text");
                        $name =  end($name);
                        $this->updateCustomerField($request->phoneNumber, "name", $name);
                        //store user session
                        $this->storeUserSession($request, "NIN");
                        return $this->writeResponse("Please enter your valid nin number", false);
                        break;
                    case "NIN":
                        $nin = explode("*", $request->text);
                        $nin =  end($nin);
                        //remove any spaces
                        $nin = str_replace(" ", "", $nin);
                        //check if nin is empty
                        if ($nin == "") {
                            return $this->writeResponse("Please enter your valid nin number", false);
                        }
                        //check if nin is less than 14
                        if (strlen($nin) < 14) {
                            return $this->writeResponse("Please enter your valid nin number", false);
                        }
                        $res = $this->validateNinNumber($nin);
                        // die($res);
                        if (!$res) {
                            $this->storeUserSession($request, "NIN");
                            return $this->writeResponse("Please enter your valid nin number", false);
                        }
                        $this->updateCustomerField($request->phoneNumber, "nin", $nin);
                        //store user session
                        $this->storeUserSession($request, "Location");
                        return $this->writeResponse("Please enter your current location", false);
                        break;
                    case "Location":
                        $location = explode("*", $request->text)[4];
                        $this->updateCustomerField($request->phoneNumber, "location", $location);
                        //store user session
                        $this->storeUserSession($request, "Terms and Conditions");
                        $response = "To Continue Please Accept our terms and conditions\n";
                        $response .= "1. Accept\n";
                        $response .= "2. Decline\n";
                        return $this->writeResponse($response, false);
                        break;
                    case "Terms and Conditions":
                        $terms =  explode("*", $request->text);
                        $actual_tems =  end($terms);
                        if ($actual_tems == "1") {
                            $this->storeUserSession($request, "PaymentNumber");
                            $total_amount = $this->getTotalAmountToPay($request->phoneNumber);
                            $response = "Total amount to pay: UGX  " . $total_amount . "\n";
                            $response .= "1. Continue\n";
                            $response .= "2. Cancel\n";
                            return $this->writeResponse($response, false);
                        } elseif ($actual_tems == "2") {
                            $this->storeUserSession($request, "Terms and Conditions");
                            return $this->writeResponse("Please you must accept our terms and conditions", false);
                        } else {
                            return $this->writeResponse("We did not understand your choice", true);
                        }
                        break;
                    case "Payment":
                        $payment_input = explode("*", $request->text);
                        $payment =  end($payment_input);
                        $this->storeUserSession($request, "Initiated Payment");
                        $this->createUserAccount($request->phoneNumber);
                        $this->createTransaction($request->phoneNumber, $this->getTotalAmountToPay($request->phoneNumber), "Payment For Subscription", $payment);
                        $response = "A payment of UGX  " . $this->getTotalAmountToPay($request->phoneNumber) . " has been initiated\n";
                        return $this->writeResponse($response, true);
                        break;
                    case "PaymentNumber":
                        $payment_input = explode("*", $request->text);
                        $payment =  end($payment_input);
                        if ($payment == "1") {
                            $session_number  = $request->phoneNumber;
                            $response = "Is this the correct payment number: " . $session_number . "\n";
                            $response .= "1. Yes\n";
                            $response .= "2. No\n";
                            $this->storeUserSession($request, "CheckPaymentNumber");
                            return $this->writeResponse($response, false);
                        } else {
                            $this->storeUserSession($request, "PaymentCancelled");
                            $response = "You have cancelled a payment of UGX  " . $this->getTotalAmountToPay($request->phoneNumber) . "\n";
                            return $this->writeResponse($response, true);
                        }
                        break;
                        break;
                    case "CheckPaymentNumber":
                        $payment_input = explode("*", $request->text);
                        $payment =  end($payment_input);
                        if ($payment == "1") {
                            $this->storeUserSession($request, "Initiated Payment");
                            $this->createUserAccount($request->phoneNumber);
                            $this->createTransaction($request->phoneNumber, $this->getTotalAmountToPay($request->phoneNumber), "Payment For Subscription", $request->phoneNumber);
                            $response = "A payment of UGX  " . $this->getTotalAmountToPay($request->phoneNumber) . " has been initiated\n";
                            return $this->writeResponse($response, true);
                        } else {
                            $this->storeUserSession($request, "Payment");
                            $response = "Please enter your payment number for example(256781234567)\n";
                            return $this->writeResponse($response, false);
                        }
                        break;
                    default:
                        return $this->welcomeUser($request);
                        break;
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            return $this->writeResponse(false, $th->getMessage(), true);
        }
    }

    private function welcomeUser(Request $request)
    {
        try {
            //code...
            $response  = "Welcome to KAMEZA by CIC:\n";
            $response .= "(Select cover of choice)\n";
            $response .= "1.(A)UGX.14,615\n";
            $response .= "2.(B)UGX 43,843\n";
            $response .= "3.(C)UGX 73,073\n";
            $response .= "4. Help\n";

            //store user session
            $this->storeUserSession($request, "00");

            return $this->writeResponse($response, false);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->writeResponse($th->getMessage(), true);
        }
    }

    private function howManyChildren(Request $request)
    {
        $response = "How many children\n";
        $response .= "(Type 0 if no)\n";
        $response .= "1. One\n";
        $response .= "2. Two\n";
        $response .= "3. Three\n";
        //store user session
        $this->storeUserSession($request, "Children");
        return $this->writeResponse($response, false);
    }
}
