<?php

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














Route::get('paywithpaypal', array('as' => 'addmoney.paywithpaypal','uses' => 'TransactionsPaypalController@payWithPaypal',));

Route::post('paypal', array('as' => 'paypal.auth','uses' => 'TransactionsPaypalController@auth',));
Route::get('paypal.store', array('as' => 'paypal.auth.store','uses' => 'TransactionsPaypalController@storeAuth',));


Route::get('paypal', array('as' => 'paypal.get.money','uses' => 'TransactionsPaypalController@getMoney',));