<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('demo')->only(['saveSocialLinkSettings', 
        'generalConfig',
        'saveSocialConfig',
        'saveSearch',
        'saveEmail',
        'savePush',
        'saveOther',
        'saveApi']);
    }

    public function changeConfigVariable($credentials, &$change_content)
    {
        foreach ($credentials as $key => $value) {
            // Check If The Key is Present in Constants File
            if (!is_null($value)) {
                $search_text    = "'" . $key . "' => '" . config('constants.' . $key . '') . "'";
                $value_text     = "'$key' => '$value'";
                $change_content = str_replace($search_text, $value_text, $change_content);
            }
        }
    }

    public function saveSocialLinkSettings(Request $request)
    {
        $config = base_path() . '/config/constants.php';
        $this->checkConstantsConfigFile($config);

        $file           = file_get_contents($config);
        $change_content = $file; // Change content in this copy.

        $credentials = $request->except('_token');

        $this->changeConfigVariable($credentials, $change_content);

        file_put_contents($config, $change_content);
        Artisan::call('optimize:clear');

        return redirect()->to(route('admin.settings.otherSettings') . '#socialLinks')
            ->with('success', __('crud.general.updated'));
    }

    
    public function demoo()
    {
            $config = base_path() . '/config/constants.php';
            $this->checkConstantsConfigFile($config);
    
            $file           = file_get_contents($config);
            $change_content = $file; // Change content in this copy.
            if (config('constants.demo_mode') == "0") {
                $credentials = [
                    'demo_mode' => '1'
                ];
            } else {
                $credentials = [
                    'demo_mode' => '0'
                ];
            }
    
            $this->changeConfigVariable($credentials, $change_content);
    
            file_put_contents($config, $change_content);
            Artisan::call('optimize:clear');
    
            return redirect()->back()
                ->with('success', __('crud.general.updated'));
    }

    public function generalConfig(Request $request)
    {
        $config = base_path() . '/config/constants.php';
        $this->checkConstantsConfigFile($config);

        $file           = file_get_contents($config);
        $change_content = $file; // Change content in this copy.

        $credentials = $request->except('_token');

        if ($request->hasFile('site_logo')) {
            $credentials['site_logo'] = $request->file('site_logo')->store('public/config');
        } else {
            unset($credentials['site_logo']);
        }

        if ($request->hasFile('site_icon')) {
            $credentials['site_icon'] = $request->file('site_icon')->store('public/config');
        } else {
            unset($credentials['site_icon']);
        }

        $this->changeConfigVariable($credentials, $change_content);

        file_put_contents($config, $change_content);
        Artisan::call('optimize:clear');

        return redirect()->to(route('admin.settings.otherSettings'))
            ->with('success', __('crud.general.updated'));
    }

    public function saveSocialConfig(Request $request)
    {
        $config = base_path() . '/config/constants.php';
        $this->checkConstantsConfigFile($config);

        $file           = file_get_contents($config);
        $change_content = $file; // Change content in this copy.

        $credentials = $request->except('_token');

        $this->changeConfigVariable($credentials, $change_content);

        file_put_contents($config, $change_content);
        Artisan::call('optimize:clear');

        return redirect()->to(route('admin.settings.otherSettings') . '#socialConfigs')
            ->with('success', __('crud.general.updated'));
    }

    public function saveSearch(Request $request)
    {
        $config = base_path() . '/config/constants.php';
        $this->checkConstantsConfigFile($config);

        $file           = file_get_contents($config);
        $change_content = $file; // Change content in this copy.

        $credentials = $request->except('_token');

        $this->changeConfigVariable($credentials, $change_content);

        file_put_contents($config, $change_content);
        Artisan::call('optimize:clear');

        return redirect()->to(route('admin.settings.otherSettings') . '#search')
            ->with('success', __('crud.general.updated'));
    }

    public function saveApi(Request $request)
    {
        $config = base_path() . '/config/constants.php';
        $this->checkConstantsConfigFile($config);

        $file           = file_get_contents($config);
        $change_content = $file; // Change content in this copy.

        $credentials = $request->except('_token');

        $this->changeConfigVariable($credentials, $change_content);

        file_put_contents($config, $change_content);
        Artisan::call('optimize:clear');

        return redirect()->to(route('admin.settings.otherSettings') . '#api')
            ->with('success', __('crud.general.updated'));
    }

    public function saveEmail(Request $request)
    {
        $config = base_path() . '/config/constants.php';
        $this->checkConstantsConfigFile($config);

        $file           = file_get_contents($config);
        $change_content = $file; // Change content in this copy.

        $credentials = $request->except('_token');

        $this->changeConfigVariable($credentials, $change_content);

        file_put_contents($config, $change_content);
        Artisan::call('optimize:clear');

        return redirect()->to(route('admin.settings.otherSettings') . '#email')
            ->with('success', __('crud.general.updated'));
    }

    public function savePush(Request $request)
    {
        $config = base_path() . '/config/constants.php';
        $this->checkConstantsConfigFile($config);

        $file           = file_get_contents($config);
        $change_content = $file; // Change content in this copy.

        $credentials = $request->except('_token');

        if ($request->hasFile('ios_push_user_permission')) {
            if (config('constants.ios_push_user_permission') != '') {
                Storage::delete(config('constants.ios_push_user_permission'));
            }
            $credentials['ios_push_user_permission'] = $request->file('ios_push_user_permission')->store('public/config');
        } else {
            unset($credentials['ios_push_user_permission']);
        }

        if ($request->hasFile('ios_push_provider_permission')) {
            if (config('constants.ios_push_provider_permission') != '') {
                Storage::delete(config('constants.ios_push_provider_permission'));
            }
            $credentials['ios_push_provider_permission'] = $request->file('ios_push_provider_permission')->store('public/config');
        } else {
            unset($credentials['ios_push_provider_permission']);
        }

        $this->changeConfigVariable($credentials, $change_content);

        file_put_contents($config, $change_content);
        Artisan::call('optimize:clear');

        return redirect()->to(route('admin.settings.otherSettings') . '#pushNotification')
            ->with('success', __('crud.general.updated'));
    }

    public function saveOther(Request $request)
    {
        $config = base_path() . '/config/constants.php';
        $this->checkConstantsConfigFile($config);

        $file           = file_get_contents($config);
        $change_content = $file; // Change content in this copy.

        $credentials = $request->except('_token');

        $this->changeConfigVariable($credentials, $change_content);

        file_put_contents($config, $change_content);
        Artisan::call('optimize:clear');

        return redirect()->to(route('admin.settings.otherSettings') . '#other')
            ->with('success', __('crud.general.updated'));
    }

    /**
     * Creates Constants.php if it does not already exists
     *
     * @param String $config
     * @return [type]
     */
    public function checkConstantsConfigFile($config)
    {
        if (!file_exists($config)) {
            $constantFile = fopen($config, 'w');
            $data         = "<?php 
            return array(
                'site_title' => '',
                'site_logo' => '',
                'site_email' => '',
                'site_icon' => '',
                'site_copyright' => '',
                'stripe_publishable_key' => '',
                'stripe_secret_key' => '',
                'contact_number' => '',
                'sos_number' => '',
                'timezone' => '',
        
                'demo_mode' => '',
                
                'store_link_android_user' => '',
                'version_android_user' => '',
                'store_link_android_provider' => '',
                'version_android_provider' => '',
                'store_link_ios_user' => '',
                'version_ios_user' => '',
                'store_link_ios_provider' => '',
                'version_ios_provider' => '',
                'facebook_link' => '',
                'instagram_link' => '',
                'twitter_link' => '',
        
                'social_login' => '',
                'fb_public_key' => '',
                'fb_secret_key' => '',
                'fb_redirect_url' => '',
                'google_public_key' => '',
                'google_secret_key' => '',
                'google_redirect_url' => '',
        
                'provider_accept_timeout' => '',
                'provider_search_radius' => '',
                'distance_map' => '',
        
                'map_key' => '',
                'server_map_key' => '',
                'fb_app_version' => '',
                'android_sender_key' => '',
        
                'ios_push_environment' => '',
                'ios_push_user_permission' => '',
                'ios_push_provider_permission' => '',
                'ios_push_password' => '',
                'android_push_key' => '',
        
                'send_mail' => '',
                'mail_driver' => '',
                'mail_host' => '',
                'mail_port' => '',
                'mail_username' => '',
                'mail_password' => '',
                'mail_from_address' => '',
                'mail_from_name' => '',
                'mail_encryption' => '',
                'referral' => '',
                'referral_count' => '',
                'referral_amount' => '',
                'booking_id_prefix' => '',
                'currency' => '',
                'currency_exchange_key' => '',
        
                'cash_payment' => '',
                'online_payment' => '',
                'stripe_payment' => '',

                'ride_toll' => '0',
                'ride_otp' => '0',
                'cancel_charge' => '0',
        
                'payment_daily_target' => '',
                'tax_percentage' => '',
                'commission_percentage' => '',
                'agent_commission_percentage' => '',
                'peak_percentage' => '',
                'minimum_negative_balance' => '',
            );";

            fwrite($constantFile, $data);
            fclose($constantFile);
            chmod($config, 0777);
        }
    }
}
