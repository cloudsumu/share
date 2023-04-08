<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Resource\CardResource;
use App\Http\Controllers\Resource\DisputeResource;
use App\Http\Controllers\Resource\LostItemResource;
use App\Http\Controllers\Api\User\UserApiController;
use App\Http\Controllers\Api\User\RequestApiController;
use App\Http\Controllers\Resource\NotificationResource;
use App\Http\Controllers\Resource\FavouriteLocationResource;

Route::get('/settings', [UserApiController::class, 'settings']);
Route::post('/verify', [UserApiController::class, 'verify']);
Route::post('/checkemail', [UserApiController::class, 'checkUserEmail']);
Route::post('/signup', [UserApiController::class, 'signup']);
Route::post('/register_otp', [UserApiController::class, 'register_otp']);

//Route::post('/otp', 'Auth\LoginController@otp');
    Route::get('/banner', [UserApiController::class, 'banners']);

Route::post('/oauth/token', [UserApiController::class, 'login']);
Route::post('/logout', [UserApiController::class, 'logout']);
Route::get('/checkapi', [UserApiController::class, 'checkapi']);
Route::post('/checkversion', [UserApiController::class, 'CheckVersion']);

//Route::post('/auth/facebook', 'Auth\SocialLoginController@facebookViaAPI');
//Route::post('/auth/google', 'Auth\SocialLoginController@googleViaAPI');

Route::post('/reset/password', [UserApiController::class, 'reset_password']);
Route::get('/estimated/fare_without_auth', [RequestApiController::class, 'fare_without_auth']);
Route::post('/forgot/password', [UserApiController::class, 'forgot_password']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    // estimated
    Route::get('/estimated/fare', [RequestApiController::class, 'estimated_fare']);//'Api\User\UserApiController@estimated_fare');

   Route::post('/direction', [RequestApiController::class, 'getLiveDirection']);//

    // user profile
    Route::post('/change/password', [UserApiController::class, 'change_password']);//'Api\User\UserApiController@change_password');
    Route::post('/update/location', [UserApiController::class, 'update_location']);//'Api\User\UserApiController@update_location');
    Route::post('/update/language', [UserApiController::class, 'update_language']);//'Api\User\UserApiController@update_language');
    Route::get('/details', [UserApiController::class, 'details']);//'Api\User\UserApiController@details');
    Route::post('/update/profile', [UserApiController::class, 'update_profile']);//'Api\User\UserApiController@update_profile');
    Route::post('/update/details', [UserApiController::class, 'update_details']);//'Api\User\UserApiController@update_details');
    // services
    Route::get('/services', [RequestApiController::class, 'services']);//'Api\User\UserApiController@services');
    // provider
    Route::post('/rate/provider', [RequestApiController::class, 'rate_provider']);//'Api\User\UserApiController@rate_provider');

    // request
    Route::post('/send/request', [RequestApiController::class, 'send_request']);//'Api\User\UserApiController@send_request');
    Route::post('/cancel/request', [RequestApiController::class, 'cancel_request']);//'Api\User\UserApiController@cancel_request');
    Route::get('/request/check', [RequestApiController::class, 'request_status_check']);//'Api\User\UserApiController@request_status_check');
    Route::get('/show/providers', [RequestApiController::class, 'show_providers']);//'Api\User\UserApiController@show_providers');
    Route::post('/update/request', [RequestApiController::class, 'modifiy_request']);//'Api\User\UserApiController@modifiy_request');
    // history
    Route::get('/trips', [UserApiController::class, 'trips']);//'Api\User\UserApiController@trips');
    Route::get('upcoming/trips', [UserApiController::class, 'upcoming_trips']);//'Api\User\UserApiController@upcoming_trips');
    Route::get('/trip/details', [UserApiController::class, 'trip_details']);//'Api\User\UserApiController@trip_details');
    Route::get('upcoming/trip/details', [UserApiController::class, 'upcoming_trip_details']);//'Api\User\UserApiController@upcoming_trip_details');
    Route::post('extend/trip', [UserApiController::class, 'extend_trip']);//'Api\User\UserApiController@extend_trip');

    // Payment
    Route::post('/payment', [RequestApiController::class, 'reasons']);//'PaymentController@payment');
    Route::post('/payment1', [RequestApiController::class, 'payment_online']);//'Api\User\UserApiController@payment_online');

    Route::post('/add/money', [UserApiController::class, 'add_money']);//'PaymentController@add_money');

    Route::post('/addmoney', [UserApiController::class, 'addMoneyRaz']);//'Api\User\UserApiController@addMoneyRaz');
    // Payment Success
    Route::get('/payment/response', [UserApiController::class, 'response']);//'PaymentController@response');

    // Payment Failure
    Route::get('/payment/failure', function () { return 'failure'; });

    // help
    Route::get('/help', [UserApiController::class, 'help_details']);//'Api\User\UserApiController@help_details');
    // promocode
    Route::get('/promocodes_list', [UserApiController::class, 'list_promocode']);//'Api\User\UserApiController@list_promocode');
    Route::get('/promocodes', [UserApiController::class, 'promocodes']);//'Api\User\UserApiController@promocodes');
    Route::post('/promocode/add', [UserApiController::class, 'add_promocode']);//'Api\User\UserApiController@add_promocode');
    // card payment
    Route::resource('card', CardResource::class);//'Resource\CardResource');
    // card payment
    Route::resource('location', FavouriteLocationResource::class);//'Resource\FavouriteLocationResource');
    // passbook
    Route::get('/wallet/passbook', [UserApiController::class, 'wallet_passbook']);//'Api\User\UserApiController@wallet_passbook');
    Route::get('/promo/passbook', [UserApiController::class, 'promo_passbook']);//'Api\User\UserApiController@promo_passbook');

    Route::post('/test/push', [UserApiController::class, 'test']);//'Api\User\UserApiController@test');

    Route::post('/chat', [UserApiController::class, 'chatPush']);//'Api\User\UserApiController@chatPush');

    Route::get('/reasons', [RequestApiController::class, 'reasons']);//'Api\User\UserApiController@reasons');

    Route::get('/notifications/{type}', [NotificationResource::class, 'getnotify']);//'Resource\NotificationResource@getnotify');

    Route::post('/dispute-list', [DisputeResource::class, 'dispute_list']);//'Resource\DisputeResource@dispute_list');

    Route::post('/dispute', [DisputeResource::class, 'create_dispute']);//'Resource\DisputeResource@create_dispute');

    Route::post('/drop-item', [LostItemResource::class, 'saveLostItem']);//'Resource\LostItemResource@saveLostItem');

    Route::patch('/drop-item/{id}', [LostItemResource::class, 'update']);//'Resource\LostItemResource@update');

    Route::post('/payment-log', [UserApiController::class, 'payment_log']);//'Api\User\UserApiController@payment_log');
});

Route::post('/verify-credentials', [UserApiController::class, 'verifyCredentials']);//'Api\User\UserApiController@verifyCredentials');

Route::post('/save-subscription/{id}', [UserApiController::class, 'reasons']);//'HomeController@save_subscription')->name('save_subscription');

    Route::post('me', function () {
        return auth()->user();
    })->middleware('auth:sanctum');
