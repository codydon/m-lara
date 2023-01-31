<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    //
    public function token(){
        $consumerKey=env('consumerKey');
        $consumerSecret=env('consumerSecret');
        $url=env('TOKENurl');

        $response=Http::withBasicAuth($consumerKey,$consumerSecret)->get($url);
        return $response['access_token'];
    }

    public function initiatePush(){
        $acessToken=$this->token();
        // dd($acessToken);
        $url=env('PUSHurl');
        // dd($url);
        $PassKey=env('passKey');
        $BusinessShortCode=env('Bcode');
        $Timestamp=Carbon::now()->format('YmdHis');
        $password=base64_encode($BusinessShortCode.$PassKey.$Timestamp);
        $TransactionType=env('Ttype');
        $Amount=env('stkAmount');
        $PartyA=env('PartyA');
        $PartyB=env('PartyB');
        $PhoneNumber=env('stkPhone');
        $CallbackUrl=env('APP_URL').'/payments/stkcallback';
        // dd($CallbackUrl);
        $AccountReference='M-LARA';
        $TransactionDesc='payment for goods';

        $response=Http::withToken($acessToken)->post($url,[
            'BusinessShortCode'=>$BusinessShortCode,
            'Password'=>$password,
            'Timestamp'=>$Timestamp,
            'TransactionType'=> $TransactionType,
            'Amount'=>$Amount,
            'PartyA'=> $PartyA,
            'PartyB'=>$PartyB,
            'PhoneNumber'=>$PhoneNumber,
            'CallBackURL'=>$CallbackUrl,
            'AccountReference'=>$AccountReference,
            'TransactionDesc'=>$TransactionDesc
        ]);

        return $response;
    }

    //for handling callback
    public function stkCallback(){
        $data=file_get_contents('php://input');
        Storage::disk('local')->put('stk.txt', $data);
    }

    //stk query
    public function stkQuery(){
        $acessToken=$this->token();
        $BusinessShortCode=env('Bcode');
        $PassKey=env('passKey');
        $url=env('stkQueryURL');
        $Timestamp=Carbon::now()->format('YmdHis');
        $password=base64_encode($BusinessShortCode.$PassKey.$Timestamp);
        $CheckoutRequestID='ws_CO_31012023221534027796976802';

        $response=Http::withToken($acessToken)->post($url, [
            'BusinessShortCode'=>$BusinessShortCode,
            'Password'=>$password,
            'Timestamp'=>$Timestamp,
            'CheckoutRequestID'=>$CheckoutRequestID
        ]);
        
        return $response;

    }

    public function registerURL(){
        $acessToken=$this->token();
        $url=env('rURL');
        $ShortCode=env('sCode');
        $ResponseType='completed'; //cancelled
        $ConfirmationURL=env('APP_URL').'/payments/confirmation';
        $ValidationURL=env('APP_URL').'/payments/validation';

        $response=Http::withToken($acessToken)->post($url, [
            'ShortCode'=>$ShortCode,
            'ResponseType'=>$ResponseType,
            'ConfirmationURL'=>$ConfirmationURL,
            'ValidationURL'=>$ValidationURL

        ]);

        return $response;
    }

    public function Validation(){
        $data=file_get_contents('php://input');
        Storage::disk('local')->put('validation.txt', $data);

        //validation logic
        // return response()->json([
        //     'ResultCode'=>0,
        //     'ResultDesc'=>'Accepted'
        // ])

        // return response()->json([
        //     'ResultCode'=>C2B00011,
        //     'ResultDesc'=>'Rejected'
        // ])

    }

    public function Confirmation(){
        $data=file_get_contents('php://input');
        Storage::disk('local')->put('confirmation.txt', $data);
        //save to DB
    }
}
