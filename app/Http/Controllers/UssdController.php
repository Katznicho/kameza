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
        // if ($this->checkIfCustomerHasAccount($request->phoneNumber)) {
        //     return $this->processCustomerWithccount($request);
        // } else {
        //     return $this->processCustomerWithOutAccount($request);
        // }

        return $this->processCustomerWithOutAccount($request);
    }


    public function processCustomerWithOutAccount(Request $request)
    {

        try {
            $this->checkIfCustomerExists($request->phoneNumber);
            if ($request->text == "") {
                return $this->registrationType($request);
            } else {
                $last_response =  $this->getLastUserSession($request->phoneNumber);
                switch ($last_response->last_user_code) {
                    case '00':
                        if ($request->text == "1") {
                            return $this->welcomeUser($request);
                        } elseif ($request->text == "2") {
                            $response = "Enter Agent ID\n";
                            $this->storeUserSession($request, "AgentID");
                            return $this->writeResponse($response, false);
                        } else {
                            return $this->writeResponse("We did not understand your choice", true);
                        }
                    case "Plan":
                        $text =  explode("*", $request->text);
                        $text = end($text);
                        if ($text == "1") {
                            $this->createUserAccount($request->phoneNumber, 1);
                            return $this->howManyChildren($request);
                        } elseif ($text == "2") {
                            $this->createUserAccount($request->phoneNumber, 2);
                            return $this->howManyChildren($request);
                        } elseif ($text == "3") {
                            $this->createUserAccount($request->phoneNumber, 3);
                            return $this->howManyChildren($request);
                        } elseif ($text == "4") {
                            return $this->writeResponse("You seleted help", true);
                        } else {
                            return $this->writeResponse("We did not understand your choice", true);
                        }
                    case "AgentID":
                        $agent_id =  explode("*", $request->text);
                        $agent_id = end($agent_id);
                        $check_agent = $this->checkIfAgentExists($agent_id);
                        if ($check_agent) {
                            $agent_details = $this->getAgentDetails($agent_id);
                            $this->updateCustomerAgent($request->phoneNumber, $agent_details->id);
                            return $this->welcomeUser($request);
                        } else {
                            $response = "Please Enter Valid Agent ID\n";
                            $this->storeUserSession($request, "AgentID");
                            return $this->writeResponse($response, false);
                        }

                    case "Children":
                        $children = $request->text;
                        $children_num =  explode("*", $children);
                        $children_num = end($children_num);
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
                        // $this->storeUserSession($request, "Name");
                        // return $this->writeResponse("Please enter your name", false);
                        $this->storeUserSession($request, "Terms and Conditions");
                        $response = "To Continue Please Accept our terms and conditions\n";
                        $response .= "1. Accept\n";
                        $response .= "2. Decline\n";
                        return $this->writeResponse($response, false);
                    case "Name":
                        $name = explode("*", "$request->text");
                        $name =  end($name);
                        $this->updateCustomerField($request->phoneNumber, "name", $name);
                        //store user session
                        $this->storeUserSession($request, "NIN");
                        return $this->writeResponse("Please enter your valid nin number", false);
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
                        if (!$res) {
                            $this->storeUserSession($request, "NIN");
                            return $this->writeResponse("Please enter your valid nin number", false);
                        }
                        $this->updateCustomerField($request->phoneNumber, "nin", $nin);
                        //store user session
                        $this->storeUserSession($request, "Location");
                        return $this->writeResponse("Please enter your current location", false);
                    case "Location":
                        $location = explode("*", $request->text)[4];
                        $this->updateCustomerField($request->phoneNumber, "location", $location);
                        //store user session
                        $this->storeUserSession($request, "Terms and Conditions");
                        $response = "To Continue Please Accept our terms and conditions\n";
                        $response .= "1. Accept\n";
                        $response .= "2. Decline\n";
                        return $this->writeResponse($response, false);
                    case "Terms and Conditions":
                        $terms =  explode("*", $request->text);
                        $actual_tems =  end($terms);
                        if ($actual_tems == "1") {
                            $this->storeUserSession($request, "PaymentNumber");
                            $total_amount = $this->getTotalAmountToPay($request->phoneNumber);
                            $this->updateCustomerField($request->phoneNumber, "amount", $total_amount);
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
                    case "Payment":
                        $payment_input = explode("*", $request->text);
                        $payment =  end($payment_input);
                        $this->storeUserSession($request, "Initiated Payment");
                        $this->createUserAccount($request->phoneNumber, 2);
                        $this->createTransaction($request->phoneNumber, $this->getTotalAmountToPay($request->phoneNumber), "Payment For Subscription", $payment);
                        $response = "A payment of UGX  " . $this->getTotalAmountToPay($request->phoneNumber) . " has been initiated\n";
                        return $this->writeResponse($response, true);
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
                    case "CheckPaymentNumber":
                        $payment_input = explode("*", $request->text);
                        $payment =  end($payment_input);
                        if ($payment == "1") {
                            $this->storeUserSession($request, "Initiated Payment");
                            $res = $this->createTransaction($request->phoneNumber, $this->getTotalAmountToPay($request->phoneNumber), "Payment For Subscription", $request->phoneNumber);
                            $this->updateCustomerField($request->phoneNumber, "is_active", true);
                            $response = "A payment of UGX  " . $this->getTotalAmountToPay($request->phoneNumber) . " has been initiated\n";
                            $response .= "An sms has been sent to your phone number on how to complete registration\n";
                            $message = "Please use this short code to complete regisration through sms\n";
                            $message .= "Short Code: 22884 \n";
                            $message .= "Type your name,nin and location in the following format\n";
                            $message .= "name,nin,location\n";
                            $message .= "for example: Katende Nicholas,CM1267546Y484,Naalya\n";
                            $message .= "Thank you for using KAMEZA\n";
                            $this->sendCompleteRegisrationMessage("+256756976723", $message);
                            return $this->writeResponse($response, true);
                        } else {
                            $this->storeUserSession($request, "Payment");
                            $response = "Please enter your payment number for example(256781234567)\n";
                            return $this->writeResponse($response, false);
                        }
                    default:
                        return $this->welcomeUser($request);
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            return $this->writeResponse(false, $th->getMessage(), true);
        }
    }

    private function registrationType(Request $request)
    {
        $response  = "Welcome to KAMEZA by CIC:\n";
        $response .= "(select  registration type)\n";
        $response .= "1. Self \n";
        $response .= "2. Agent\n";
        $this->storeUserSession($request, "00");
        return $this->writeResponse($response, false);
    }
    private function welcomeUser(Request $request)
    {
        try {
            //code...
            $response  = "Select cover of choice:\n";
            $response .= "1.(A)UGX.14,615\n";
            $response .= "2.(B)UGX 43,843\n";
            $response .= "3.(C)UGX 73,073\n";
            $response .= "4. Help\n";

            //store user session
            $this->storeUserSession($request, "Plan");

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

    public function completeRegistrationThroughSms(Request $request)
    {
        try {
            //code...
            $message =  explode(",", $request->text);

            if (empty($message)) {
                $this->storeMessageSession($request, "Failed", "No information provided");
                $this->sendCompleteRegisrationMessage($request->from, "Please enter your details in the format name,nin,location for example: Katende Nicholas,CM1267546Y484,Naalya");
                return response()->json([], 200);
            }

            //check if the array length is 3
            if (count($message) != 3) {
                $this->storeMessageSession($request, "Failed", "No information provided");
                $this->sendCompleteRegisrationMessage($request->from, "Please enter your details in the format name,nin,location for example: Katende Nicholas,CM1267546Y484,Naalya");
                return response()->json([], 200);
            }

            $name = $message[0];
            $nin  = $message[1];
            $location = $message[2];
            //store the response in laravellogs
            \Illuminate\Support\Facades\Log::info($request->all());

            //check if non is empty
            if ($name == "") {
                $this->storeMessageSession($request, "Failed", "No name provided");
                $this->sendCompleteRegisrationMessage($request->from, "Please enter your name");
                return response()->json([], 200);
            }
            if ($nin == "") {
                $this->storeMessageSession($request, "Failed", "No nin provided");
                $this->sendCompleteRegisrationMessage($request->from, "Please enter your nin");
                return response()->json([], 200);
            }
            if ($location == "") {
                $this->storeMessageSession($request, "Failed", "No location provided");
                $this->sendCompleteRegisrationMessage($request->from, "Please enter your location");
                return response()->json([], 200);
            }
            $nin = str_replace("", "", $nin);

            //check if  nin has 14 digits
            if (strlen($nin) < 14) {
                $nin_l = strlen($nin);
                $this->storeMessageSession($request, "Failed", "Invalid nin number: $nin_l");
                $this->sendCompleteRegisrationMessage($request->from, "Please enter a valid nin");
                return response()->json([], 200);
            } else {
                $this->updateCustomerDetails($request->from, $nin, $name, $location);
                $this->storeMessageSession($request, "Success", "User Completed Registration");
                $this->sendCompleteRegisrationMessage($request->from, "Your registration was successful. Thank you for using our service");
                //send back a status of 200
                return response()->json([], 200);
            }
        } catch (\Throwable $th) {
            //throw $th;
            $this->sendCompleteRegisrationMessage($request->from, "Please try again and enter your details in the format name,nin,location for example: Katende Nicholas,CM1267546Y484,Naalya");
            return response()->json([], 200);
        }
    }
}
