<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\C2brequest;


class PaymentController extends Controller
{
    //
    public function token()
    {
        $consumerKey = env('consumerKey');
        $consumerSecret = env('consumerSecret');
        $url = env('TOKENurl');

        $response = Http::withBasicAuth($consumerKey, $consumerSecret)->get($url);
        return $response['access_token'];
    }

    public function initiatePush()
    {
        $acessToken = $this->token();
        // dd($acessToken);
        $url = env('PUSHurl');
        // dd($url);
        $PassKey = env('passKey');
        $BusinessShortCode = env('Bcode');
        $Timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($BusinessShortCode . $PassKey . $Timestamp);
        $TransactionType = env('Ttype');
        $Amount = env('stkAmount');
        $PartyA = env('PartyA');
        $PartyB = env('PartyB');
        $PhoneNumber = env('stkPhone');
        $CallbackUrl = env('APP_URL') . '/payments/stkcallback';
        // dd($CallbackUrl);
        $AccountReference = 'M-LARA';
        $TransactionDesc = 'payment for goods';

        $response = Http::withToken($acessToken)->post($url, [
            'BusinessShortCode' => $BusinessShortCode,
            'Password' => $password,
            'Timestamp' => $Timestamp,
            'TransactionType' => $TransactionType,
            'Amount' => $Amount,
            'PartyA' => $PartyA,
            'PartyB' => $PartyB,
            'PhoneNumber' => $PhoneNumber,
            'CallBackURL' => $CallbackUrl,
            'AccountReference' => $AccountReference,
            'TransactionDesc' => $TransactionDesc
        ]);

        return $response;
    }

    //for handling callback
    public function stkCallback()
    {
        $data = file_get_contents('php://input');
        Storage::disk('local')->put('stk.txt', $data);
    }

    //stk query
    public function stkQuery()
    {
        $acessToken = $this->token();
        $BusinessShortCode = env('Bcode');
        $PassKey = env('passKey');
        $url = env('stkQueryURL');
        $Timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($BusinessShortCode . $PassKey . $Timestamp);
        $CheckoutRequestID = 'ws_CO_31012023221534027796976802';

        $response = Http::withToken($acessToken)->post($url, [
            'BusinessShortCode' => $BusinessShortCode,
            'Password' => $password,
            'Timestamp' => $Timestamp,
            'CheckoutRequestID' => $CheckoutRequestID
        ]);

        return $response;
    }

    public function registerURL()
    {
        $acessToken = $this->token();
        $url = env('rURL');
        $ShortCode = env('sCode');
        $ResponseType = 'completed'; //cancelled
        $ConfirmationURL = env('APP_URL') . '/payments/confirmation';
        $ValidationURL = env('APP_URL') . '/payments/validation';

        $response = Http::withToken($acessToken)->post($url, [
            'ShortCode' => $ShortCode,
            'ResponseType' => $ResponseType,
            'ConfirmationURL' => $ConfirmationURL,
            'ValidationURL' => $ValidationURL

        ]);

        return $response;
    }

    //c2b
    public function Simulate()
    {
        $accessToken = $this->token();
        $url = env('c2bURL');
        $ShortCode = env('c2bShortCode');
        $CommandID = 'CustomerPayBillOnline'; //CustomerBuyGoodsOnline
        $Amount = env('c2bAmt');
        $Msisdn = env('Msisdn');
        $BillRefNumber = env('BillRefNumber');

        $response = Http::withToken($accessToken)->post($url, [
            'ShortCode' => $ShortCode,
            'CommandID' => $CommandID,
            'Amount' => $Amount,
            'Msisdn' => $Msisdn,
            'BillRefNumber' => $BillRefNumber
        ]);

        return $response;
    }

    public function Validation()
    {
        $data = file_get_contents('php://input');
        Storage::disk('local')->put('validation.txt', $data);

        //validation logic
        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);

        // return response()->json([
        //     'ResultCode'=>C2B00011,
        //     'ResultDesc'=>'Rejected'
        // ]);

    }

    public function Confirmation()
    {
        $data = file_get_contents('php://input');
        Storage::disk('local')->put('confirmation.txt', $data);

        //confirmation logic



        $response = json_decode($data);

        //save to DB
        $TransactionType = $response->TransactionType;
        $TransID = $response->TransID;
        $TransTime = $response->TransTime;
        $TransAmount = $response->TransAmount;
        $BusinessShortCode = $response->BusinessShortCode;
        $BillRefNumber = $response->BillRefNumber;
        $InvoiceNumber = $response->InvoiceNumber;
        $OrgAccountBalance = $response->OrgAccountBalance;
        $ThirdPartyTransID = $response->ThirdPartyTransID;
        $MSISDN = $response->MSISDN;
        $FirstName = $response->FirstName;
        $MiddleName = $response->MiddleName;
        $LastName = $response->LastName;

        $c2b=new C2brequest;
        $c2b->TransactionType=$TransactionType;
        $c2b->TransID=$TransID;
        $c2b->TransTime=$TransTime;
        $c2b->TransAmount=$TransAmount;
        $c2b->BusinessShortCode=$BusinessShortCode;
        $c2b->BillRefNumber=$BillRefNumber;
        $c2b->InvoiceNumber=$InvoiceNumber;
        $c2b->OrgAccountBalance=$OrgAccountBalance;
        $c2b->ThirdPartyTransID=$ThirdPartyTransID;
        $c2b->MSISDN=$MSISDN;
        $c2b->FirstName=$FirstName;
        $c2b->MiddleName=$MiddleName;
        $c2b->LastName=$LastName;
        $c2b->save();


        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted'
        ]);
    }


    //B2C
    public function b2c(){
        $accessToken=$this->token();
        $InitiatorName='testapi';
        $InitiatorPassword=env('InitiatorPassword');
        $path=Storage::disk('local')->get('SandboxCertificate.cer');
        $pk=openssl_pkey_get_public($path);

        openssl_public_encrypt(
            $InitiatorPassword,
            $encrypted,
            $pk,
            $padding=OPENSSL_PKCS1_PADDING
        );

        //$encrypted
        $SecurityCredential=base64_encode($encrypted);
        $CommandID='SalaryPayment'; //BusinessPayment, PromotionPayment
        $Amount=env('b2cAmt');
        $PartyA=env('b2cA');
        $PartyB=env('b2cB');
        $Remarks='remarks';  //up to 100 characters
        $QueueTimeOutURL=env('APP_URL').'/payments/b2ctimeout';
        $resultURL=env('APP_URL').'/payments/b2cresult';
        // dd($resultURL);
        $Occasion='occasion'; //up to 100 characters
        $url=env('b2cURL');

        $response=Http::withToken($accessToken)->post($url, [
            'InitiatorName'=>$InitiatorName,
            'SecurityCredential'=>$SecurityCredential,
            'CommandID'=>$CommandID,
            'Amount'=>$Amount,
            'PartyA'=>$PartyA,
            'PartyB'=>$PartyB,
            'Remarks'=>$Remarks,
            'QueueTimeOutURL'=>$QueueTimeOutURL,
            'resultURL'=>$resultURL,
            'Occasion'=>$Occasion
        ]);

        return $response;
    }

    public function b2cResult(){
        dd('result');
        $data=file_get_contents('php://input');
        Storage::disk('local')->put('b2cresponse.txt', $data);
    }

    public function b2cTimeout(){
        $data=file_get_contents('php://input');
        Storage::disk('local')->put('b2ctimeout.txt', $data);
    }
}
