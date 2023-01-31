<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::controller(PaymentController::class)
->prefix('payments')
->as('payments')
->group(function(){
    Route::get('/token', 'token')->name('token');
    Route::get('/initiatepush', 'initiatePush')->name('initiatepush');
    Route::post('/stkcallback', 'stkCallback')->name('stkcallback');
    Route::get('/stkquery', 'stkQuery')->name('stkquery');
    Route::get('/registerurl', 'registerURL')->name('registerurl');
    Route::post('/validation', 'Validation')->name('validation');
    Route::post('/confirmation', 'Cnfirmation')->name('confirmation');
});