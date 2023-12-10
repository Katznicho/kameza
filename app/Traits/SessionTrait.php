<?php

namespace App\Traits;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait SessionTrait
{
    public function storeUserSession(Request $request,  string $lastUserCode)
    {
        //store user session
        DB::table('ussd_sesions')->insert([
            'phone_number' => $request->phoneNumber,
            'session_id' => $request->sessionId,
            'text' => $request->text,
            'network_code' => $request->networkCode,
            'service_code' => $request->serviceCode,
            'last_user_code' => $lastUserCode
        ]);
        return true;
    }

    //get session details
    public function getLastUserSession(string $phoneNumber)
    {
        $lastUserSession = DB::table('ussd_sesions')
            ->where('phone_number', $phoneNumber)
            ->orderBy('id', 'desc')
            ->first();

        return $lastUserSession;
    }

    public function storeMessageSession(Request $request, string $status, string $message)
    {
       //store message session
        DB::table('message_sessions')->insert([
            'linkId' => $request->linkId,
            'text' => $request->text,
            'to' => $request->to,
            'message_id' => $request->message_id,
            'date' => $request->date,
            'from' => $request->from,
            'status' => $status,
            'message' => $message
        ]);
        return true;
    }
}
