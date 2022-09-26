<?php

use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;


if(!function_exists("getApiUser")){
    function getApiUser(){
        return JWTAuth::parseToken()->authenticate();
    }
}

if(!function_exists("getLoanSchedule")){
    function getLoanSchedule($loan_amount,$term){
        $payment_date = Carbon::now();
        $data_scheds = [];

        $payment_amount = round(($loan_amount/$term),2);
        for ($i = 1; $i <= $term; $i++)
        {
            if($i < $term){
                $balance_owed = $loan_amount - $payment_amount;
            }else{
                $payment_amount = $loan_amount;
            }

            $payment_date = $payment_date->addDays(7);

            $tmp = [];
            $tmp["payment_date"] = $payment_date->format('Y-m-d');
            $tmp["payment_amount"] = $payment_amount;

            $data_scheds[] = $tmp;
            $loan_amount = $balance_owed;
        }
        return $data_scheds;
    }
}







