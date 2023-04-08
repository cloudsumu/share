<?php

namespace App\Http\Controllers\Resource;

use DB;
use Validator;
use App\Models\User;
use App\Models\Provider;
use App\Models\ReferralEarning;
use App\Models\ReferralHistory;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TransactionResource;

class ReferralResource extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function checkReferralCode(array $data)
    {
        $rules = [
            'referral_unique_id'  => 'unique:users|unique:providers',
        ];

        $messages = [
            'referral_unique_id.unique'  => 'referral_code_already exits',
        ];

        return Validator::make($data, $rules, $messages);
    }

    public function generateCode($length = 6)
    {
        $az                        = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $azr                       = rand(0, 51);
        $azs                       = substr($az, $azr, 10);
        $stamp                     = hash('sha256', time());
        $mt                        = hash('sha256', mt_rand(5, 20));
        $alpha                     = hash('sha256', $azs);
        $hash                      = str_shuffle($stamp . $mt . $alpha);
        $code                      = strtoupper(substr($hash, $azr, $length));
        $data['referral_unique_id']=$code;

        $validator  = $this->checkReferralCode($data);

        if ($validator->fails()) {
            $this->generateCode();
        }

        return $code;
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function create_referral($referral_code, $referral_data)
    {
        \Log::alert("1 referal");
        $type    =1;
        $referral=User::where('referral_unique_id', $referral_code)->first();
        

        if (empty($referral)) {
            $type    =2;
            $referral=Provider::where('referral_unique_id', $referral_code)->first();
        }
        \Log::alert($referral);
        if (!empty($referral)) {
            //insert referral histroy
            $User = ReferralHistory::create([
                'referrer_id'   => $referral->id,
                'type'          => $type,
                'referral_id'   => $referral_data->id,
                'referral_data' => json_encode($referral_data),
                'status'        => 'P',
            ]);
            \Log::alert(config('constants.referral_amount'));
            if ((int)config('constants.referral_amount') > 0) {
                $ReferralHistory=ReferralHistory::select(DB::raw('group_concat(id) as ids'))->where('referrer_id', $referral->id)->where('type', $type)->where('status', 'P')->groupBy('referrer_id')->get();
                \Log::alert("inside amount");
                \Log::alert($ReferralHistory->count());
                $Referralcount=0;
                $ids          =null;

                if ($ReferralHistory->count() > 0) {
                    $Referralcount=count(explode(',', $ReferralHistory[0]->ids));
                    $ids          =$ReferralHistory[0]->ids;
                }
                if (config('constants.referral_count') > 0 && config('constants.referral_count') <= $Referralcount) {
                    //create referral earnings
                    $Earnings = ReferralEarning::create([
                        'referrer_id'         => $referral->id,
                        'type'                => $type,
                        'amount'              => config('constants.referral_amount', 0),
                        'count'               => $Referralcount,
                        'referral_histroy_id' => $ids,
                    ]);
                    \Log::alert($Earnings);
                    //create amount to user/provider wallet
                    (new TransactionResource())->referralCreditDebit((int)config('constants.referral_amount'), $referral->id, $type);

                    //update histroy table process status to complete
                    ReferralHistory::where('referrer_id', $referral->id)->where('type', $type)->where('status', 'P')->update(['status' => 'C']);
                }
            }
        }
    }

    public function get_referral($type, $user_id)
    {
        $ReferralHistory=ReferralEarning::where('referrer_id', $user_id)->where('type', ($type =='user' ?1:2))->get([
            DB::raw('COALESCE(SUM(count), 0) AS total_count'),
            DB::raw('COALESCE(SUM(amount), 0) AS total_amount'),
        ]);
        return $ReferralHistory;
    }
}
