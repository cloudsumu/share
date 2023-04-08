<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Agent;
use App\Models\Provider;
use App\Models\UserWallet;
use App\Models\AdminWallet;
use App\Models\AgentWallet;
use App\Models\UserRequest;
use Illuminate\Http\Request;
use App\Models\WalletRequest;
use App\Models\ProviderWallet;
use App\Models\UserRequestPayment;
use Illuminate\Support\Facades\DB;

class TransactionResource extends Controller
{
    /*
      check the payment status is completed or not
      if its completed check the below logics
      Check the request table if user have any commission
      check the request table if provider have any agent
      check the user, applied any discount
      check the payment mode is cash, card, wallet, partial
      check whether provider have any negative balance
     */

    public function callTransaction($request_id)
    {
        $UserRequest = UserRequest::with('provider')->with('payment')->findOrFail($request_id);

        if ($UserRequest->paid == 1) {
            if (config('constants.send_email', 0) == 1) {
                //    Helpers::site_sendmail($UserRequest);
            }
            $paymentsRequest = UserRequestPayment::where('request_id', $request_id)->first();

            $provider = Provider::where('id', $paymentsRequest->provider_id)->first();

            $agent_amount = $discount = $admin_commision = $credit_amount = $balance_provider_credit = $provider_credit = 0;

            if ($paymentsRequest->is_partial == 1) {
                //partial payment
                if ($paymentsRequest->payment_mode == 'CASH') {
                    $credit_amount = $paymentsRequest->wallet + $paymentsRequest->tips;
                } else {
                    $credit_amount = $paymentsRequest->total + $paymentsRequest->tips;
                }
            } else {
                //TODO ALLAN - Carteira
                if ($paymentsRequest->payment_mode == 'CARD' || $paymentsRequest->payment_mode == 'VOUCHER' || $paymentsRequest->payment_id == 'WALLET') {
                    $credit_amount = $paymentsRequest->total + $paymentsRequest->tips;
                } else {
                    $credit_amount = 0;
                }
            }

            //admin,agent,provider calculations
            if (!empty($paymentsRequest->commision_per)) {
                $admin_commision = $paymentsRequest->commision;

                if (!empty($paymentsRequest->agent_id)) {
                    //get the percentage of agent owners
                    $agent           = Agent::where('id', $paymentsRequest->agent_id)->first();
                    $agent_per       = config('constants.agent_commission_percentage');
                    $agent_amount    = ($admin_commision) * ($agent_per / 100);
                    $admin_commision = $admin_commision -  $agent_amount;
                }

                //check the user applied discount
                if (!empty($paymentsRequest->discount)) {
                    $balance_provider_credit = $paymentsRequest->discount;
                }
            } else {
                if (!empty($paymentsRequest->agent_id)) {
                    $agent_per       = config('constants.agent_commission_percentage');
                    $agent_amount    = ($paymentsRequest->total) * ($agent_per / 100);
                    $admin_commision = $agent_amount;
                }
                if (!empty($paymentsRequest->discount)) {
                    $balance_provider_credit = $paymentsRequest->discount;
                }
            }

            if (!empty($admin_commision)) {
                //add the commission amount to admin wallet and debit amount to provider wallet, update the provider wallet amount to provider table
                $this->adminCommission($admin_commision, $paymentsRequest, $UserRequest);
            }

            if (!empty($paymentsRequest->agent_id) && !empty($agent_amount)) {
                $paymentsRequest->commision = $admin_commision;
                $paymentsRequest->agent     = $agent_amount;
                $paymentsRequest->agent_per = $agent_per;
                $paymentsRequest->save();
                //create the amount to agent account and deduct the amount to admin wallet, update the agent wallet amount to agent table
                $this->agentCommission($agent_amount, $paymentsRequest, $UserRequest);
            }
            if (!empty($balance_provider_credit)) {
                //debit the amount to admin wallet and add the amount to provider wallet, update the provider wallet amount to provider table
                $this->providerDiscountCredit($balance_provider_credit, $paymentsRequest, $UserRequest);
            }

            if (!empty($paymentsRequest->tax)) {
                //debit the amount to provider wallet and add the amount to admin wallet
                $this->taxCredit($paymentsRequest->tax, $paymentsRequest, $UserRequest);
            }

            if (!empty($paymentsRequest->peak_comm_amount)) {
                //add the peak amount commision to admin wallet
                $this->peakAmount($paymentsRequest->peak_comm_amount, $paymentsRequest, $UserRequest);
            }

            if (!empty($paymentsRequest->waiting_comm_amount)) {
                //add the waiting amount commision to admin wallet
                $this->waitingAmount($paymentsRequest->waiting_comm_amount, $paymentsRequest, $UserRequest);
            }

            if ($credit_amount > 0) {
                //provider ride amount
                //check whether provider have any negative wallet balance if its deduct the amount from its credit.
                //if its negative wallet balance grater of its credit amount then deduct credit-wallet balance and update the negative amount to admin wallet
                if ($provider->wallet_balance > 0) {
                    $admin_amount = $credit_amount - ($admin_commision + $paymentsRequest->tax);
                } else {
                    $admin_amount = $credit_amount - ($admin_commision + $paymentsRequest->tax) + ($provider->wallet_balance);
                }

                $this->providerRideCredit($credit_amount, $admin_amount, $paymentsRequest, $UserRequest);
            }

            return true;
        } else {
            return true;
        }
    }

    public function createAdminWallet($request)
    {
        $admin_data = AdminWallet::orderBy('id', 'DESC')->first();

        $adminwallet                    = new AdminWallet();
        $adminwallet->transaction_id    = $request['transaction_id'];
        $adminwallet->transaction_alias = $request['transaction_alias'];
        $adminwallet->transaction_desc  = $request['transaction_desc'];
        $adminwallet->transaction_type  = $request['transaction_type'];
        $adminwallet->type              = $request['type'];
        $adminwallet->amount            = $request['amount'];

        if (empty($admin_data->close_balance)) {
            $adminwallet->open_balance = 0;
        } else {
            $adminwallet->open_balance = $admin_data->close_balance;
        }

        if (empty($admin_data->close_balance)) {
            $adminwallet->close_balance = $request['amount'];
        } else {
            $adminwallet->close_balance = $admin_data->close_balance + ($request['amount']);
        }

        $adminwallet->save();

        return $adminwallet;
    }

    public function createUserWallet($request)
    {
        $user = User::findOrFail($request['id']);

        $userWallet                    = new UserWallet();
        $userWallet->user_id           = $request['id'];
        $userWallet->transaction_id    = $request['transaction_id'];
        $userWallet->transaction_alias = $request['transaction_alias'];
        $userWallet->transaction_desc  = $request['transaction_desc'];
        $userWallet->type              = $request['type'];
        $userWallet->amount            = $request['amount'];

        if (empty($user->wallet_balance)) {
            $userWallet->open_balance = 0;
        } else {
            $userWallet->open_balance = $user->wallet_balance;
        }

        if (empty($user->wallet_balance)) {
            $userWallet->close_balance = $request['amount'];
        } else {
            $userWallet->close_balance = $user->wallet_balance + ($request['amount']);
        }

        $userWallet->save();

        //update the user wallet amount to user table
        $user->wallet_balance = $user->wallet_balance + ($request['amount']);
        $user->save();

        return $userWallet;
    }

    public function createProviderWallet($request)
    {
        $provider = Provider::findOrFail($request['id']);

        $providerWallet                    = new ProviderWallet();
        $providerWallet->provider_id       = $request['id'];
        $providerWallet->transaction_id    = $request['transaction_id'];
        $providerWallet->transaction_alias = $request['transaction_alias'];
        $providerWallet->transaction_desc  = $request['transaction_desc'];
        $providerWallet->type              = $request['type'];
        $providerWallet->amount            = $request['amount'];

        if (empty($provider->wallet_balance)) {
            $providerWallet->open_balance = 0;
        } else {
            $providerWallet->open_balance = $provider->wallet_balance;
        }

        if (empty($provider->wallet_balance)) {
            $providerWallet->close_balance = $request['amount'];
        } else {
            $providerWallet->close_balance = $provider->wallet_balance + ($request['amount']);
        }

        $providerWallet->save();

        //update the provider wallet amount to provider table
        $provider->wallet_balance = $provider->wallet_balance + ($request['amount']);
        $provider->save();

        return $providerWallet;
    }

    public function createAgentWallet($request)
    {
        $agent = Agent::findOrFail($request['id']);

        $agentWallet                    = new AgentWallet();
        $agentWallet->agent_id          = $request['id'];
        $agentWallet->transaction_id    = $request['transaction_id'];
        $agentWallet->transaction_alias = $request['transaction_alias'];
        $agentWallet->transaction_desc  = $request['transaction_desc'];
        $agentWallet->type              = $request['type'];
        $agentWallet->amount            = $request['amount'];

        if (empty($agent->wallet_balance)) {
            $agentWallet->open_balance = 0;
        } else {
            $agentWallet->open_balance = $agent->wallet_balance;
        }

        if (empty($agent->wallet_balance)) {
            $agentWallet->close_balance = $request['amount'];
        } else {
            $agentWallet->close_balance = $agent->wallet_balance + ($request['amount']);
        }

        $agentWallet->save();

        //update the agent wallet amount to agent table
        $agent->wallet_balance = $agent->wallet_balance + ($request['amount']);
        $agent->save();

        return true;
    }

    public function adminCommission($amount, $paymentsRequest, $UserRequest)
    {
        $ipdata                      = [];
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.admin_commission');
        $ipdata['transaction_type']  = 1;
        $ipdata['type']              = 'C';
        $ipdata['amount']            = $amount;
        $this->createAdminWallet($ipdata);

        $provider_det_amt            = -1 * abs($amount);
        $ipdata                      = [];
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.admin_commission');
        $ipdata['id']                = $paymentsRequest->provider_id;
        $ipdata['type']              = 'D';
        $ipdata['amount']            = $provider_det_amt;
        $this->createProviderWallet($ipdata);
    }

    public function agentCommission($amount, $paymentsRequest, $UserRequest)
    {
        $ipdata                      = [];
        $admin_det_amt               = -1 * abs($amount);
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.agent_debit');
        $ipdata['transaction_type']  = 7;
        $ipdata['type']              = 'D';
        $ipdata['amount']            = $admin_det_amt;
        $this->createAdminWallet($ipdata);

        $ipdata                      = [];
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.agent_add');
        $ipdata['id']                = $paymentsRequest->agent_id;
        $ipdata['type']              = 'C';
        $ipdata['amount']            = $amount;
        $this->createAgentWallet($ipdata);

        $ipdata                      = [];
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.agent_recharge');
        $ipdata['transaction_type']  = 6;
        $ipdata['type']              = 'C';
        $ipdata['amount']            = $amount;
        $this->createAdminWallet($ipdata);

        return true;
    }

    public function providerDiscountCredit($amount, $paymentsRequest, $UserRequest)
    {
        $ipdata                      = [];
        $ad_det_amt                  = -1 * abs($amount);
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.discount_apply');
        $ipdata['transaction_type']  = 10;
        $ipdata['type']              = 'D';
        $ipdata['amount']            = $ad_det_amt;
        $this->createAdminWallet($ipdata);

        $ipdata                      = [];
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.discount_refund');
        $ipdata['id']                = $paymentsRequest->provider_id;
        $ipdata['type']              = 'C';
        $ipdata['amount']            = $amount;
        $this->createProviderWallet($ipdata);

        (new SendPushNotification())->sendPushToProvider($paymentsRequest->provider_id, 'Você recebeu ' . config('constants.currency') . $amount . ' na sua carteira!');

        $ipdata                      = [];
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.discount_recharge');
        $ipdata['transaction_type']  = 11;
        $ipdata['type']              = 'C';
        $ipdata['amount']            = $amount;
        $this->createAdminWallet($ipdata);

        return true;
    }

    public function taxCredit($amount, $paymentsRequest, $UserRequest)
    {
        $ipdata                      = [];
        $ad_det_amt                  = -1 * abs($amount);
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.tax_credit');
        $ipdata['id']                = $paymentsRequest->provider_id;
        $ipdata['type']              = 'D';
        $ipdata['amount']            = $ad_det_amt;
        $this->createProviderWallet($ipdata);

        $ipdata                      = [];
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.tax_debit');
        $ipdata['transaction_type']  = 9;
        $ipdata['type']              = 'C';
        $ipdata['amount']            = $amount;
        $this->createAdminWallet($ipdata);

        return true;
    }

    public function waitingAmount($amount, $paymentsRequest, $UserRequest)
    {
        $ipdata                      = [];
        $ad_det_amt                  = -1 * abs($amount);
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.waiting_commission');
        $ipdata['id']                = $paymentsRequest->provider_id;
        $ipdata['type']              = 'D';
        $ipdata['amount']            = $ad_det_amt;
        $this->createProviderWallet($ipdata);

        $ipdata                      = [];
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.waiting_commission');
        $ipdata['transaction_type']  = 15;
        $ipdata['type']              = 'C';
        $ipdata['amount']            = $amount;
        $this->createAdminWallet($ipdata);

        return true;
    }

    public function peakAmount($amount, $paymentsRequest, $UserRequest)
    {
        $ipdata                      = [];
        $ad_det_amt                  = -1 * abs($amount);
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.peak_commission');
        $ipdata['id']                = $paymentsRequest->provider_id;
        $ipdata['type']              = 'D';
        $ipdata['amount']            = $ad_det_amt;
        $this->createProviderWallet($ipdata);

        $ipdata                      = [];
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.peak_commission');
        $ipdata['transaction_type']  = 14;
        $ipdata['type']              = 'C';
        $ipdata['amount']            = $amount;
        $this->createAdminWallet($ipdata);

        return true;
    }

    public function providerRideCredit($amount, $admin_amount, $paymentsRequest, $UserRequest)
    {
        $ipdata                      = [];
        $ipdata['transaction_id']    = $UserRequest->id;
        $ipdata['transaction_alias'] = $UserRequest->booking_id;
        $ipdata['transaction_desc']  = trans('api.transaction.provider_credit');
        $ipdata['id']                = $paymentsRequest->provider_id;
        $ipdata['type']              = 'C';
        $ipdata['amount']            = $amount;
        $this->createProviderWallet($ipdata);

        //(new SendPushNotification())->sendPushToProvider($paymentsRequest->provider_id, 'You received ' . config('constants.currency') . $amount . 'in your wallet!');

        if ($admin_amount > 0) {
            $ipdata                      = [];
            $ipdata['transaction_id']    = $UserRequest->id;
            $ipdata['transaction_alias'] = $UserRequest->booking_id;
            $ipdata['transaction_desc']  = trans('api.transaction.provider_recharge');
            $ipdata['transaction_type']  = 4;
            $ipdata['type']              = 'C';
            $ipdata['amount']            = $admin_amount;
            $this->createAdminWallet($ipdata);
        }

        return true;
    }

    public function transationAlias($userType, $paymentType = null)
    {
        if ($userType == 'user') {
            $user_data = UserWallet::orderBy('id', 'DESC')->first();
            $prefix    = ($paymentType != null) ? 'RFU' : 'URC';
        } else {
            $user_data = ProviderWallet::orderBy('id', 'DESC')->first();
            $prefix    = ($paymentType != null) ? 'RFP' : 'PRC';
        }

        if (!empty($user_data)) {
            $transaction_id = $user_data->id + 1;
        } else {
            $transaction_id = 1;
        }

        return $prefix . str_pad($transaction_id, 6, 0, STR_PAD_LEFT);
    }

    public function userCreditDebit($amount, $UserRequest, $type = 1)
    {
        \Log::info($amount . ' ' . $UserRequest . '' . $type);
        if ($type == 1) {
            $msg       = trans('api.transaction.user_recharge');
            $ttype     = 'C';
            $user_data = UserWallet::orderBy('id', 'DESC')->first();
            if (!empty($user_data)) {
                $transaction_id = $user_data->id + 1;
            } else {
                $transaction_id = 1;
            }

            $transaction_alias = $this->transationAlias('user');

            $user_id          = $UserRequest;
            $transaction_type = 2;
        } else {
            if ($type = 3) {
                $msg  = trans('api.transaction.user_trip_cancel');
            }else{
                $msg  = trans('api.transaction.user_trip');
            }
            $ttype             = 'D';
            $amount            = -1 * abs($amount);
            $transaction_id    = $UserRequest->id;
            $transaction_alias = $UserRequest->booking_id;
            $user_id           = $UserRequest->user_id;
            $transaction_type  = 3;
        }

        $ipdata                      = [];
        $ipdata['transaction_id']    = $transaction_id;
        $ipdata['transaction_alias'] = $transaction_alias;
        $ipdata['transaction_desc']  = $msg;
        $ipdata['transaction_type']  = $transaction_type;
        $ipdata['type']              = $ttype;
        $ipdata['amount']            = $amount;
        $this->createAdminWallet($ipdata);

        $ipdata                      = [];
        $ipdata['transaction_id']    = $transaction_id;
        $ipdata['transaction_alias'] = $transaction_alias;
        $ipdata['transaction_desc']  = $msg;
        $ipdata['id']                = $user_id;
        $ipdata['type']              = $ttype;
        $ipdata['amount']            = $amount;
        return $this->createUserWallet($ipdata);
    }

    public function providerCreditDebit($amount, $UserRequest, $type = 1)
    {
        if ($type == 1) {
            $msg       = trans('api.transaction.user_recharge');
            $ttype     = 'C';
            $user_data = ProviderWallet::orderBy('id', 'DESC')->first();
            if (!empty($user_data)) {
                $transaction_id = $user_data->id + 1;
            } else {
                $transaction_id = 1;
            }

            $transaction_alias = $this->transationAlias('provider');

            $user_id          = $UserRequest;
            $transaction_type = 2;
        } else {
            $msg               = trans('api.transaction.user_trip');
            $ttype             = 'D';
            $amount            = -1 * abs($amount);
            $transaction_id    = $UserRequest->id;
            $transaction_alias = $UserRequest->booking_id;
            $user_id           = $UserRequest->user_id;
            $transaction_type  = 3;
        }

        $ipdata                      = [];
        $ipdata['transaction_id']    = $transaction_id;
        $ipdata['transaction_alias'] = $transaction_alias;
        $ipdata['transaction_desc']  = $msg;
        $ipdata['transaction_type']  = $transaction_type;
        $ipdata['type']              = $ttype;
        $ipdata['amount']            = $amount;
        $this->createAdminWallet($ipdata);

        $ipdata                      = [];
        $ipdata['transaction_id']    = $transaction_id;
        $ipdata['transaction_alias'] = $transaction_alias;
        $ipdata['transaction_desc']  = $msg;
        $ipdata['id']                = $user_id;
        $ipdata['type']              = $ttype;
        $ipdata['amount']            = $amount;
        return $this->createProviderWallet($ipdata);
    }

    public function referralCreditDebit($amount, $UserRequest, $type = 1)
    {
        if ($type == 1) {
            $msg       = trans('api.transaction.referal_recharge');
            $ttype     = 'C';
            $user_data = UserWallet::orderBy('id', 'DESC')->first();
            if (!empty($user_data)) {
                $transaction_id = $user_data->id + 1;
            } else {
                $transaction_id = 1;
            }

            $transaction_alias = $this->transationAlias('user', 'refer');

            $user_id          = $UserRequest;
            $transaction_type = 12;

            $ipdata                      = [];
            $ipdata['transaction_id']    = $transaction_id;
            $ipdata['transaction_alias'] = $transaction_alias;
            $ipdata['transaction_desc']  = $msg;
            $ipdata['id']                = $user_id;
            $ipdata['type']              = $ttype;
            $ipdata['amount']            = $amount;
            $this->createUserWallet($ipdata);
        } else {
            $msg       = trans('api.transaction.referal_recharge');
            $ttype     = 'C';
            $user_data = ProviderWallet::orderBy('id', 'DESC')->first();
            if (!empty($user_data)) {
                $transaction_id = $user_data->id + 1;
            } else {
                $transaction_id = 1;
            }

            $transaction_alias = $this->transationAlias('user', 'refer');
            $user_id           = $UserRequest;
            $transaction_type  = 13;

            $ipdata                      = [];
            $ipdata['transaction_id']    = $transaction_id;
            $ipdata['transaction_alias'] = $transaction_alias;
            $ipdata['transaction_desc']  = $msg;
            $ipdata['id']                = $user_id;
            $ipdata['type']              = $ttype;
            $ipdata['amount']            = $amount;
            $this->createProviderWallet($ipdata);
        }

        $ipdata                      = [];
        $ipdata['transaction_id']    = $transaction_id;
        $ipdata['transaction_alias'] = $transaction_alias;
        $ipdata['transaction_desc']  = $msg;
        $ipdata['transaction_type']  = $transaction_type;
        $ipdata['type']              = 'D';
        $ipdata['amount']            = -1 * abs($amount);
        $this->createAdminWallet($ipdata);

        return true;
    }

    public function disputeCreditDebit($amount, $UserRequest, $type = 1)
    {
        if ($type == 1) {
            $msg       = trans('api.transaction.dispute_refund');
            $ttype     = 'C';
            $user_data = UserWallet::orderBy('id', 'DESC')->first();
            if (!empty($user_data)) {
                $transaction_id = $user_data->id + 1;
            } else {
                $transaction_id = 1;
            }

            $transaction_alias = 'DPU' . str_pad($transaction_id, 6, 0, STR_PAD_LEFT);

            $user_id          = $UserRequest;
            $transaction_type = 16;

            $ipdata                      = [];
            $ipdata['transaction_id']    = $transaction_id;
            $ipdata['transaction_alias'] = $transaction_alias;
            $ipdata['transaction_desc']  = $msg;
            $ipdata['id']                = $user_id;
            $ipdata['type']              = $ttype;
            $ipdata['amount']            = $amount;
            $this->createUserWallet($ipdata);
        } else {
            $msg       = trans('api.transaction.dispute_refund');
            $ttype     = 'C';
            $user_data = ProviderWallet::orderBy('id', 'DESC')->first();
            if (!empty($user_data)) {
                $transaction_id = $user_data->id + 1;
            } else {
                $transaction_id = 1;
            }

            $transaction_alias = 'DPP' . str_pad($transaction_id, 6, 0, STR_PAD_LEFT);
            $user_id           = $UserRequest;
            $transaction_type  = 17;

            $ipdata                      = [];
            $ipdata['transaction_id']    = $transaction_id;
            $ipdata['transaction_alias'] = $transaction_alias;
            $ipdata['transaction_desc']  = $msg;
            $ipdata['id']                = $user_id;
            $ipdata['type']              = $ttype;
            $ipdata['amount']            = $amount;
            $this->createProviderWallet($ipdata);
        }

        $ipdata                      = [];
        $ipdata['transaction_id']    = $transaction_id;
        $ipdata['transaction_alias'] = $transaction_alias;
        $ipdata['transaction_desc']  = $msg;
        $ipdata['transaction_type']  = $transaction_type;
        $ipdata['type']              = 'D';
        $ipdata['amount']            = -1 * abs($amount);
        $this->createAdminWallet($ipdata);

        return true;
    }

    public function requestamount(Request $request)
    {
        // Total amount of Pending settlement requests
        $premat = WalletRequest::where('from_id', auth()->user()->id)->where('request_from', $request->type)->where('status', 0)->sum('amount');

        $available = auth()->user()->wallet_balance - $premat;

        $messsages = [
            'amount.max' => trans('api.amount_max') . config('constants.currency', '$') . $available,
        ];
        $this->validate($request, [
            'amount' => 'required|numeric|min:1|max:' . $available,
        ], $messsages);
        try {
            $nextid                      = generate_request_id($request->type);
            $amountRequest               = new WalletRequest();
            $amountRequest->alias_id     = $nextid;
            $amountRequest->request_from = $request->type;
            $amountRequest->from_id      = auth()->user()->id;
            $amountRequest->type         = 'D';
            if (config('constants.card', 0) == 1) {
                $amountRequest->send_by = 'online';
            } else {
                $amountRequest->send_by = 'offline';
            }
            $amountRequest->amount = round($request->amount, 2);
            $amountRequest->save();
            $fn_response['success'] = trans('api.amount_success');
        } catch (\Illuminate\Database\QueryException $e) {
            $fn_response['error'] = $e->getMessage();
        } catch (Exception $e) {
            $fn_response['error'] = $e->getMessage();
        }

        return response()->json($fn_response);
    }

    public function requestcancel(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|numeric',
        ]);
        try {
            $amountRequest         = WalletRequest::find($request->id);
            $amountRequest->status = 2;
            $amountRequest->save();
            $fn_response['success'] = trans('api.amount_cancel');
        } catch (\Illuminate\Database\QueryException $e) {
            $fn_response['error'] = $e->getMessage();
        } catch (Exception $e) {
            $fn_response['error'] = $e->getMessage();
        }

        return response()->json($fn_response);
    }
}
