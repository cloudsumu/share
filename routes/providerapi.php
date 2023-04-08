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
use App\Http\Controllers\Resource\DisputeResource;
use App\Http\Controllers\Resource\LostItemResource;
use App\Http\Controllers\Api\Provider\TripController;
use App\Http\Controllers\Api\Provider\TokenController;
use App\Http\Controllers\Resource\NotificationResource;
use App\Http\Controllers\Resource\ProviderCardResource;
use App\Http\Controllers\Api\Provider\ProfileController;

Route::get('/settings', [TokenController::class, 'settings']);//'ProviderAuth\TokenController@settings');
// Authentication
Route::post('/register', [TokenController::class, 'register']);//'ProviderAuth\TokenController@register');
Route::post('/oauth/token', [TokenController::class, 'authenticate']);//'ProviderAuth\TokenController@authenticate');
Route::post('/logout', [TokenController::class, 'logout']);//'ProviderAuth\TokenController@logout');
Route::post('/verify', [TokenController::class, 'verify']);//'ProviderAuth\TokenController@verify');
Route::post('/auth/facebook', [TokenController::class, 'facebookViaAPI']);//'ProviderAuth\TokenController@facebookViaAPI');
Route::post('/auth/google', [TokenController::class, 'googleViaAPI']);//'ProviderAuth\TokenController@googleViaAPI');
Route::post('/forgot/password', [TokenController::class, 'forgot_password']);//'ProviderAuth\TokenController@forgot_password');
Route::post('/reset/password', [TokenController::class, 'reset_password']);//'ProviderAuth\TokenController@reset_password');

Route::get('/refresh/token', [TokenController::class, 'refresh_token']);//'ProviderAuth\TokenController@refresh_token');
Route::post('update-location', [TripController::class, 'track_location']);//'ProviderResources\TripController@track_location');
Route::get('get-location', [TripController::class, 'track_location_get']);//'ProviderResources\TripController@track_location_get');
Route::get('remove-location', [TripController::class, 'track_location_remove']);//'ProviderResources\TripController@track_location_remove');

Route::group(['middleware' => ['auth:sanctum']], function () {
    // Payment
    // Route::post('/payment', 'PaymentController@payment');
    // Route::post('/add/money', 'PaymentController@add_money');

    Route::post('/addmoney', [ProfileController::class, 'addMoneyRazPro']);//'ProviderResources\ProfileController@addMoneyRazPro');

    Route::post('/payment-log', [TripController::class, 'track_location_remove']);//'UserApiController@payment_log');
    Route::post('/direction', [TripController::class,'getLiveDirection']);
    // Braintree
    Route::get('/braintree/token', [TripController::class, 'track_location_remove']);//'UserApiController@client_token');

    // Payu
    Route::post('/payu/response', [TripController::class, 'track_location_remove']);//'PaymentController@payu_response');
    Route::post('/payu/failure', [TripController::class, 'track_location_remove']);//'PaymentController@payu_error');

    // Payment Success
    Route::get('/payment/success', [TripController::class, 'track_location_remove']);//'PaymentController@response');

    // Payment Failure
    Route::get('/payment/failure', function () { return 'failure'; });

    //Route::post('/refresh/token' , 'ProviderAuth\TokenController@refresh_token');

    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [ProfileController::class, 'index']);//'ProviderResources\ProfileController@index');
        Route::post('/', [ProfileController::class, 'update']);//'ProviderResources\ProfileController@update');
        Route::post('/password', [ProfileController::class, 'password']);//'ProviderResources\ProfileController@password');
        Route::post('/location', [ProfileController::class, 'location']);//'ProviderResources\ProfileController@location');
        Route::post('/language', [ProfileController::class, 'update_language']);//'ProviderResources\ProfileController@update_language');
        Route::post('/available', [ProfileController::class, 'available']);//'ProviderResources\ProfileController@available');
        Route::get('/documents', [ProfileController::class, 'documents']);//'ProviderResources\ProfileController@documents');
        Route::post('/documents/store', [ProfileController::class, 'documentstore']);//'ProviderResources\ProfileController@documentstore');
        Route::post('/details', [ProfileController::class, 'update_details']);//'ProviderResources\ProfileController@update_details');
    });

    Route::resource('providercard', ProviderCardResource::class);//'Resource\ProviderCardResource');

    Route::post('/chat', [ProfileController::class, 'chatPush']);//'ProviderResources\ProfileController@chatPush');

    Route::get('/target', [ProfileController::class, 'target']);//'ProviderResources\ProfileController@target');
    Route::resource('trip', TripController::class);//'ProviderResources\TripController');
    Route::post('cancel', [TripController::class, 'cancel']);//'ProviderResources\TripController@cancel');
    Route::post('summary', [TripController::class, 'summary']);//'ProviderResources\TripController@summary');
    Route::get('help', [TripController::class, 'help_details']);//'ProviderResources\TripController@help_details');
    Route::get('/wallettransaction', [TripController::class, 'wallet_transation']);//'ProviderResources\TripController@wallet_transation');
    Route::get('/wallettransaction/details', [TripController::class, 'wallet_details']);//'ProviderResources\TripController@wallet_details');
    Route::get('/transferlist', [TripController::class, 'transferlist']);//'ProviderResources\TripController@transferlist');
    Route::post('/requestamount', [TripController::class, 'requestamount']);//'ProviderResources\TripController@requestamount');
    Route::get('/requestcancel', [TripController::class, 'requestcancel']);//'ProviderResources\TripController@requestcancel');
    Route::post('/waiting', [TripController::class, 'waiting']);//'ProviderResources\TripController@waiting');

    Route::group(['prefix' => 'trip'], function () {
        Route::post('{id}', [TripController::class, 'accept']);//'ProviderResources\TripController@accept');
        Route::post('{id}/rate', [TripController::class, 'rate']);//'ProviderResources\TripController@rate');
        Route::post('{id}/message', [TripController::class, 'message']);//'ProviderResources\TripController@message');
        Route::post('{id}/calculate', [TripController::class, 'calculate_distance']);//'ProviderResources\TripController@calculate_distance');
    });

    Route::post('requests/rides', [TripController::class, 'request_rides']);//'ProviderResources\TripController@request_rides');

    Route::group(['prefix' => 'requests'], function () {
        Route::post('/instant/ride', [TripController::class, 'instant_ride']);//'ProviderResources\TripController@instant_ride');
        Route::get('/upcoming', [TripController::class, 'scheduled']);//'ProviderResources\TripController@scheduled');
        Route::get('/history', [TripController::class, 'history']);//'ProviderResources\TripController@history');
        Route::get('/history/details', [TripController::class, 'history_details']);//'ProviderResources\TripController@history_details');
        Route::get('/upcoming/details', [TripController::class, 'upcoming_details']);//'ProviderResources\TripController@upcoming_details');
    });

    Route::post('/test/push', [TripController::class, 'test']);//'ProviderResources\TripController@test');

    Route::get('/reasons', [ProfileController::class, 'reasons']);//'ProviderResources\ProfileController@reasons');

    Route::get('/notifications/{type}', [NotificationResource::class, 'getnotify']);//'Resource\NotificationResource@getnotify');

    Route::post('/dispute-list', [DisputeResource::class, 'dispute_list']);//'Resource\DisputeResource@dispute_list');

    Route::post('/dispute', [DisputeResource::class, 'create_dispute']);//'Resource\DisputeResource@create_dispute');

    Route::post('/drop-item', [LostItemResource::class, 'store']);//'Resource\LostItemResource@store');
});

Route::post('/verify-credentials', [ProfileController::class, 'verifyCredentials']);//'ProviderResources\ProfileController@verifyCredentials');
