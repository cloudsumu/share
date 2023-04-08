<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\MapController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\DisputeController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\PeakHourController;
use App\Http\Controllers\Admin\ProviderController;
use App\Http\Controllers\Admin\PromocodeController;
use App\Http\Controllers\Admin\StatementController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\CustomPushController;
use App\Http\Controllers\Admin\GeoFencingController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SettlementController;
use App\Http\Controllers\Admin\DisputeTypeController;
use App\Http\Controllers\Admin\ServiceTypeController;
use App\Http\Controllers\Admin\CancelReasonController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\FrontendSettingController;
use App\Http\Controllers\Admin\ProviderDocumentController;
use App\Http\Controllers\Admin\ServiceRentalHourController;

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

// Authentication Routes
Route::prefix('/')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm']);
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('loginForm');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Other Routes
Route::prefix('/')->middleware('auth:admin')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('home');
    Route::view('translation', 'admin.translations')->name('translations');
    Route::view('telescope', 'admin.telescope')->name('telescope');

    // Dispatcher
    Route::get('/dispatcher', [HomeController::class, 'dispatcher'])->name('dispatcherDashboard');
    Route::get('/demoo', [ConfigController::class, 'demoo']);
    
    // Map
    Route::get('/map', [MapController::class, 'index'])->name('map.index');
    Route::get('/heatMap', [MapController::class, 'heatMap'])->name('map.heatMap');

    Route::get('/otherSettings', [HomeController::class, 'settings'])->name('settings.otherSettings');
    Route::get('/pushTest', [HomeController::class, 'pushtest']);
    
    // User Request
    // Route::get('/userRequest/{userRequest}/show', [HomeController::class, 'userRequest'])->name('userRequest.show');
    Route::get('requestHistory', [HomeController::class, 'requestHistory'])->name('request.history');
    Route::get('requestDetail/{userRequest}', [HomeController::class, 'requestDetail'])->name('request.detail');

    // Constants Config File
    Route::post('/settings/generalConfig', [ConfigController::class, 'generalConfig'])->name('settings.generalConfig');
    Route::post('/settings/saveSocialLinks', [ConfigController::class, 'saveSocialLinkSettings'])->name('settings.storeSocialLinks');
    Route::post('/settings/saveSocialConfig', [ConfigController::class, 'saveSocialConfig'])->name('settings.storeSocialConfig');
    Route::post('/settings/saveSearch', [ConfigController::class, 'saveSearch'])->name('settings.storeSearch');
    Route::post('/settings/saveApi', [ConfigController::class, 'saveApi'])->name('settings.storeApi');
    Route::post('/settings/saveEmail', [ConfigController::class, 'saveEmail'])->name('settings.storeEmail');
    Route::post('/settings/storePushNotification', [ConfigController::class, 'savePush'])->name('settings.storePushNotification');
    Route::post('/settings/saveOther', [ConfigController::class, 'saveOther'])->name('settings.storeOthers');

    // Frontend Settings File
    Route::get('/settings/frontend', [FrontendSettingController::class, 'index'])->name('settings.frontend.index');
    Route::post('/settings/frontend/top', [FrontendSettingController::class, 'headSetting'])->name('settings.frontend.headSetting');
    Route::post('/settings/frontend/steps', [FrontendSettingController::class, 'steps'])->name('settings.frontend.steps');
    Route::post('/settings/frontend/appDisplay', [FrontendSettingController::class, 'appDisplay'])->name('settings.frontend.appDisplay');

    // Database
    Route::get('tnc', [HomeController::class, 'tnc'])->name('settings.tnc');
    Route::post('/settings/tnc', [HomeController::class, 'saveTnc'])->name('settings.saveTnc');
    Route::get('privacy', [HomeController::class, 'privacy'])->name('settings.privacy');
    Route::post('/settings/privacyPolicy', [HomeController::class, 'savePrivacy'])->name('settings.savePrivacy');

    Route::get('/profileSettings', [HomeController::class, 'profileSettings'])->name('profileSettings');
    Route::post('/changePassword', [HomeController::class, 'changePassword'])->name('changePassword');
    Route::post('/updateProfile', [HomeController::class, 'updateProfile'])->name('updateProfile');
    Route::get('/language/{lang}', [HomeController::class, 'language'])->name('updateLanguage');
    

    // Statements
    Route::get('/statements/overall/{for?}/{id?}', [StatementController::class, 'index'])->name('statement.overall');
    Route::get('/statements/provider', [StatementController::class, 'providerStatements'])->name('statement.provider');
    Route::get('/statements/user', [StatementController::class, 'userStatements'])->name('statement.user');
    Route::get('/statements/agent', [StatementController::class, 'agentStatements'])->name('statement.agent');

    // Payment
    Route::get('/payment/history', [PaymentController::class, 'paymentHistory'])->name('payment.history');
    Route::get('/payment/setting', [PaymentController::class, 'paymentSetting'])->name('payment.setting');
    Route::post('/payment/setting', [PaymentController::class, 'savePaymentSetting'])->name('payment.saveSetting');
    Route::post('/payment/methodSetting', [PaymentController::class, 'savePaymentMethodSetting'])->name('payment.savePaymentMethodSetting');

    // Settlements - New Settlement Controller
    Route::get('settlements', [SettlementController::class, 'index'])->name('settlement.index');
    Route::get('settlements/create', [SettlementController::class, 'create'])->name('settlement.create');
    Route::post('settlements/store', [SettlementController::class, 'store'])->name('settlement.store');
    Route::get('/transactions', [PaymentController::class, 'allTransactions'])->name('settlement.allTransaction');

    // Ratings
    Route::get('/ratings', [HomeController::class, 'ratings'])->name('ratings');

    // Graphs
    Route::get('/getAdminCredits', [HomeController::class, 'getAdminCredits'])->name('getAdminCredits');
    Route::get('/getUserRequestStatus', [HomeController::class, 'getUserRequestStatus'])->name('getUserRequestStatus');

    // Handling Documents
    Route::get('/acceptDocument/{providerDocument}', [ProviderDocumentController::class, 'acceptDocument'])->name('acceptProviderDocument');
    Route::delete('/rejectDocument/{providerDocument}', [ProviderDocumentController::class, 'rejectDocument'])->name('rejectProviderDocument');

    // Handling Provider
    Route::put('/blockProvider/{provider}', [ProviderController::class, 'blockProvider'])->name('provider.blockProvider');
    Route::put('/approveProvider/{provider}', [ProviderController::class, 'approveProvider'])->name('provider.approveProvider');
    
    // Dispute Routes
    Route::resource('dispute', DisputeController::class)->only([
        'index', 'edit', 'update'
    ]);

    // Resource Route
    Route::resource('user', UserController::class);
    Route::resource('faq', FaqController::class);
    Route::resource('admin', AdminController::class);
    Route::resource('provider', ProviderController::class);
    Route::resource('agent', AgentController::class);
    Route::resource('role', RoleController::class);
    Route::resource('geoFencing', GeoFencingController::class);
    Route::resource('document', DocumentController::class);
    Route::resource('permission', PermissionController::class);
    Route::resource('disputeType', DisputeTypeController::class);
    Route::resource('serviceType', ServiceTypeController::class);
    Route::get('serviceType/{serviceType}/subServices', [ServiceTypeController::class, 'subServices'])->name('subServices');
    Route::resource('cancelReason', CancelReasonController::class);
    Route::resource('banner', BannerController::class);
    Route::resource('peakHour', PeakHourController::class);
    Route::resource('notification', NotificationController::class);
    Route::resource('promocode', PromocodeController::class);
    Route::resource('customPush', CustomPushController::class);

    Route::resource('providerSettlement', SettlementController::class)->except([
        'create', 'store',
    ]);
});