<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\Auth\LoginController;
use App\Http\Controllers\User\Auth\RegisterController;
use App\Http\Controllers\User\Auth\SocialAuthentication;
use App\Http\Controllers\User\ServiceController;

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

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/contactUs', [HomeController::class, 'contactUs'])->name('contactUs');
Route::get('/tnc', [HomeController::class, 'tnc'])->name('tnc');
Route::get('/privacyPolicy', [HomeController::class, 'privacy'])->name('privacy');

// Route::get('/blog', [HomeController::class, 'blog'])->name('blog');
// Route::get('/geoFence/{geoFencing}/serviceType/{serviceType}', [HomeController::class, 'service'])->name('geoFenceServices');
// Route::get('/getService/{serviceType}', [HomeController::class, 'getServiceAjax'])->name('getServiceAjax');

// Type hint this in controller
// Route::get('/blog/{blog}', [HomeController::class, 'showBlog'])->name('viewBlog');

// Authentication Routes
// Route::name('user.')->prefix('user/')->group(function () {

//     // Manual
//     Route::get('/login', [LoginController::class, 'showLoginForm'])->name('loginForm');
//     Route::post('/login', [LoginController::class, 'login'])->name('login');
//     Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('registrationForm');
//     Route::post('/register', [RegisterController::class, 'register'])->name('register');
//     Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
// });

// // Google
// Route::name('user.')->middleware('guest')->group(function() {
//     // Google
//     Route::get('/user/sign-up/google', [SocialAuthentication::class, 'googleLogin'])->name('google.registration');
//     Route::get('/user/sign-in/google', [SocialAuthentication::class, 'googleLogin'])->name('google.login');
//     Route::get('google/success', [SocialAuthentication::class, 'googleRedirect']);
// });

// // User Routes After Authentication
// Route::name('user.')->prefix('user/')->middleware('auth:web')->group(function() {
//     Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('home');
//     Route::get('{geoFence}/{serviceType}/checkout/', [ServiceController::class, 'serviceCheckout'])->name('serviceCheckout');
// });