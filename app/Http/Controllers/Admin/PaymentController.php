<?php

namespace App\Http\Controllers\Admin;

use App\Models\PaymentLog;
use App\Models\AdminWallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use App\Http\Requests\UpdatePaymentSettingsRequest;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('demo')->only(['savePaymentSetting', 
        'savePaymentMethodSetting']);
    }
    public function paymentHistory()
    {
        $paymentLogs = PaymentLog::latest()
            ->paginate();
        return view('admin.payment.paymentHistory', compact('paymentLogs'));
    }

    public function paymentSetting()
    {
        return view('admin.payment.paymentSetting');
    }

    public function savePaymentSetting(UpdatePaymentSettingsRequest $request)
    {
        $settings = new ConfigController();

        $config = base_path() . '/config/constants.php';
        $settings->checkConstantsConfigFile($config);

        $file           = file_get_contents($config);
        $change_content = $file; // Change content in this copy.

        $credentials = $request->except('_token');

        $settings->changeConfigVariable($credentials, $change_content);

        file_put_contents($config, $change_content);
        Artisan::call('optimize:clear');

        return redirect()
            ->back()
            ->withSuccess(__('crud.general.updated'));
    }

    public function savePaymentMethodSetting(Request $request)
    {

        $settings = new ConfigController();

        $config = base_path() . '/config/constants.php';
        $settings->checkConstantsConfigFile($config);

        $file           = file_get_contents($config);
        $change_content = $file; // Change content in this copy.

        $credentials = $request->except('_token');
        
        if(!$request->has('online_payment') && !$request->has("cash_payment") && !$request->has('stripe_payment')) {
            return redirect()
                ->back()
                ->withErrors('No Payment Method Selected. Atleast one of the Payment method should be selected.');
        }

        if($request->has('stripe_payment') && $request->has('online_payment')) {
            return redirect()
                ->back()
                ->withErrors('You can either have online or cash payment method.');
        }

        if($request->has('online_payment')) {
            $credentials['online_payment'] = 1;
        }
        else {
            $credentials['online_payment'] = 0;
        }

        if($request->has('stripe_payment')) {
            $credentials['stripe_payment'] = 1;
        }
        else {
            $credentials['stripe_payment'] = 0;
        }

        if($request->has('cash_payment')) {
            $credentials['cash_payment'] = 1;
        }
        else {
            $credentials['cash_payment'] = 0;
        }

        // dd($credentials);

        $settings->changeConfigVariable($credentials, $change_content);

        file_put_contents($config, $change_content);
        Artisan::call('optimize:clear');

        return redirect()
            ->back()
            ->withSuccess(__('crud.general.updated'));
    }

    // public function providerSettlements()
    // {
    //     $paymentLogs = PaymentLog::where('user_type' ,'provider')
    //         ->latest()
    //         ->paginate();
    //     return view('admin.payment.providerSettlement', compact('paymentLogs'));
    // }

    public function allTransactions()
    {
        $paymentLogs = AdminWallet::orderBy('id', 'desc')
            ->paginate();
        return view('admin.payment.allTransactions', compact('paymentLogs'));
    }
}
