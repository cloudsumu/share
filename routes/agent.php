<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Agent\HomeController;
use App\Http\Controllers\Agent\ProfileController;
use App\Http\Controllers\Agent\ProviderController;
use App\Http\Controllers\Agent\Auth\LoginController;
use App\Http\Controllers\Agent\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register agent routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

// Route::get('/blog', [HomeController::class, 'blog'])->name('blog');
// Route::get('/geoFence/{geoFencing}/serviceType/{serviceType}', [HomeController::class, 'service'])->name('geoFenceServices');
// Route::get('/getService/{serviceType}', [HomeController::class, 'getServiceAjax'])->name('getServiceAjax');

// Type hint this in controller
// Route::get('/blog/{blog}', [HomeController::class, 'showBlog'])->name('viewBlog');

// Manual Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('loginForm');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('registrationForm');
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// // Google
// Route::name('user.')->middleware('guest')->group(function() {
//     // Google
//     Route::get('/user/sign-up/google', [SocialAuthentication::class, 'googleLogin'])->name('google.registration');
//     Route::get('/user/sign-in/google', [SocialAuthentication::class, 'googleLogin'])->name('google.login');
//     Route::get('google/success', [SocialAuthentication::class, 'googleRedirect']);
// });

// Agent Routes After Authentication
// Route::name('agent.')->prefix('user/')->middleware('auth:web')->group(function() {
//     Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('home');
//     Route::get('{geoFence}/{serviceType}/checkout/', [ServiceController::class, 'serviceCheckout'])->name('serviceCheckout');
// });

// Provider Routes After Authentication
Route::middleware('auth:agent')->group(function() {
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('home');
    Route::get('/language/{lang}', [HomeController::class, 'language'])->name('updateLanguage');

    Route::get('/showUserRequest/{userRequest}', [HomeController::class, 'showUserRequest'])->name('showUserRequest');

    // -- copied

    Route::get('/statement', [HomeController::class, 'statement'])->name('ride.statement'); 
    Route::get('/statement/provider', [HomeController::class, 'statement_provider'])->name('ride.statement.provider');

    Route::get('map', [HomeController::class, 'map_index'])->name('map.index');
    Route::get('map/ajax', [HomeController::class, 'map_ajax'])->name('map.ajax');

    // User Rating
    // Update: Handle This Views
    Route::get('providersRating', [ProviderController::class, 'ratings'])->name('provider_ratings');

    Route::get('profile', [ProfileController::class, 'profile'])->name('profile');
    Route::post('profile', [ProfileController::class, 'profile_update'])->name('profile.update');

    Route::get('/wallet', [HomeController::class, 'wallet'])->name('wallet');
    Route::get('/transfer', [HomeController::class, 'transfer'])->name('transfer');

    Route::post('/transfer/send', [HomeController::class, 'requestAmount'])->name('requestAmount');
    Route::get('/transfer/cancel', [HomeController::class, 'cancel'])->name('cancel');

    Route::post('password', [ProfileController::class, 'password_update'])->name('password.update');

    // // Static Pages - Post updates to pages.update when adding new static pages.

    Route::get('requests', [HomeController::class, 'agentIndex'])->name('requests.index');
    Route::delete('requests/{id}', [HomeController::class, 'agentDestroy'])->name('requests.destroy');
    Route::get('requests/{id}', [HomeController::class, 'agentShow'])->name('requests.show');
    Route::get('scheduled', [HomeController::class, 'agentScheduled'])->name('requests.scheduled');

    Route::resource('provider', ProviderController::class)->only([
        'index', 'create', 'store'
    ]);
});