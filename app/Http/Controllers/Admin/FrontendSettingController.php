<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FrontendSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('demo')->only(['updateSettings', 
        'headSetting', 
        'steps', 
        'appDisplay']);
    }

    public function index()
    {
        $results = Setting::select('key', 'value')->where('key', 'like', 'home_%')->get()->toArray();
        $settings = [];
        foreach($results as $result) {
            $settings[$result['key']] = $result['value'];
        }
        return view('admin.frontend_setting', compact('settings'));
    }

    public function updateSettings($credentials)
    {
        foreach($credentials as $key => $value) { // Loop through all the keys.
            $result = Setting::where('key', $key)->first();
            
            $set['key'] = $key;
            $set['value'] = $value;

            if(!$value) {
                continue;
            }

            if($result) {
                $set['value'] = $credentials[$key];
                if($credentials[$key] instanceof UploadedFile) {
                    // This is a file. The File Already Exists since we are updating the value.
                    Storage::delete($result['value']);
                    $set['value'] = $credentials[$key]->store('public/config');
                }
                $result->update($set);
            }
            else {
                $set['key'] = $key;
                $set['value'] = $credentials[$key];
                if($credentials[$key] instanceof UploadedFile) {
                    $set['value'] = $credentials[$key]->store('public/config');
                }
                Setting::create($set);
            }
        }
    }

    public function headSetting(Request $request)
    {
        $credentials = $request->except('_token');
        $this->updateSettings($credentials);

        return redirect(route('admin.settings.frontend.index').'#header')
            ->with('success', __('crud.general.updated'));
    }

    public function steps(Request $request)
    {
        $credentials = $request->except('_token');
        $this->updateSettings($credentials);

        return redirect(route('admin.settings.frontend.index').'#steps')
            ->with('success', __('crud.general.updated'));
    }

    public function appDisplay(Request $request)
    {
        $credentials = $request->except('_token');
        $this->updateSettings($credentials);

        return redirect(route('admin.settings.frontend.index').'#appDisplay')
            ->with('success', __('crud.general.updated'));
    }
}