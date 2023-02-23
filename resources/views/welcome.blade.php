<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <title>DARAJA_GUZZLE</title>
</head>

<body class="text-center container mx-auto">
    <div class="mt-20 font-extrabold">DARAJA API IMPLEMENTATION WITH GUZZLE</div>

    <div class="mt-10">
        @if(empty($stkResponse))
        <!-- <p>empty</p> -->
        @else
        <!-- <p>STK Response:</p> -->

        <div>
            <h3>STK Response</h3>
            <p>Merchant Request ID: {{ $stkResponse['MerchantRequestID'] }}</p>
            <p>Checkout Request ID: {{ $stkResponse['CheckoutRequestID'] }}</p>
            <p>Response Code: {{ $stkResponse['ResponseCode'] }}</p>
            <p>Response Description: {{ $stkResponse['ResponseDescription'] }}</p>
            <p>Customer Message: {{ $stkResponse['CustomerMessage'] }}</p>
        </div>


        @endif

    </div>

    <hr>

    <div class="stkform bg-red-100 p-6 rounded-lg shadow-md ">
        <!-- <label class="block text-gray-700 font-bold mb-2" for="phone" class="font-bold">STK PUSH FORM :</label> -->
        <form class="" method="POST" action="{{ url('payments/initiatepush') }}">
            @csrf
            <label class="block text-gray-700 font-bold mb-2" for="phone">Phone number:</label>
            <input class="border-2 border-gray-400 p-2 w-full rounded-md mb-4" type="number" id="phone" name="phone" placeholder="254712345678" value="254796976802" required>
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md" type="submit">Initiate STK push</button>
        </form>
    </div>

    <hr>

    <div class="stkQuery mt-20 bg-green-100 p-6 rounded-lg shadow-md">
        <label class="block text-black font-bold mb-2" for="">STK QUERY CHECKER</label>
        <form class="" method="POST" action="{{ url('payments/stkquery') }}">
            @csrf
            <label class="block text-gray-700 font-bold mb-2" for="query">Query ID:</label>
            @if(empty($stkResponse))
            <input class="border-2 border-gray-400 p-2 w-full rounded-md" type="text" id="query" name="queryID" placeholder="query ID" required>
            @else
            <input class="border-2 border-gray-400 p-2 w-full rounded-md mb-4" type="text" id="query" name="queryID" placeholder="query ID" value="{{ $stkResponse['CheckoutRequestID'] }}" required>
            @endif

            <button  class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md mt-4" type="submit">Check QUery Status</button>
        </form>
    </div>

    <div class="" style="color:teal;">
        @if(empty($queryResponse))
        <!-- <p>empty</p> -->
        @else
        <div>
            <h3>Query Status:</h3>
            <p>Request ID: {{ $queryResponse['requestId'] }}</p>
            <p>Error Code: {{ $queryResponse['errorCode'] }}</p>
            <p>Error Message: {{ $queryResponse['errorMessage'] }}</p>
        </div>
        @endif
    </div>

</body>
</body>

</html>