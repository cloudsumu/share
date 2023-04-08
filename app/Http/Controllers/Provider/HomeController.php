<?php

namespace App\Http\Controllers\Provider;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
Use App\Http\Requests\{
    ChangePasswordRequest, Provider\UpdateProfileRequest, Provider\StoreVerificationDocumentsRequest
};

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

use App\Models\{
    ProviderDocument, Document
};

class HomeController extends Controller
{
    /**
     * Return the Provider Dashboard
     * 
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        return view('provider.dashboard');
    }

    /**
     * Return the Provider Settings Page
     * 
     * @return Illuminate\Http\Response
     */
    public function settings()
    {
        $documents = ProviderDocument::where('provider_id', auth()->user('provider')->id)->get();

        $ids = auth()->user('provider')->documents->pluck('document_id')->toArray();

        $required = Document::where('status', '1')->get();
        $requiredIds = $required->pluck('id')->toArray();

        $notGiven = array_diff($requiredIds, $ids);

        return view('provider.settings', compact('documents', 'required', 'ids', 'notGiven'));
    }

    /**
     * Updates the Provider Password
     * 
     * @param ChangePasswordRequest $request
     * @return Illuminate\Http\Response
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $credentials = $request->only('password');
        if (!Hash::check($request->old_password, $request->user()->password)) {
            return redirect(route('provider.settings')."#changePassword")
                ->withErrors([
                    'old_password' => 'Incorrect Old Password',
                ]);
        }

        $credentials['password'] = Hash::make($request['password']);
        $provider = $request->user('provider');
        $provider->update($credentials);

        return redirect(route('provider.settings')."#changePassword")
            ->with('success', __('crud.inputs.password')." ".__('crud.general.updated'));
    }

    /**
     * @return [type]
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $credentials = $request->validated();
        $provider = $request->user('provider');

        if ($request->hasFile('avatar')) {
            if ($provider->avatar) {
                Storage::delete($provider->avatar);
            }

            $credentials['avatar'] = $request->file('avatar')->store('public/provider/avatars');
        }

        $provider->update($credentials);

        return redirect(route('provider.settings')."#general")
            ->with('success', __('crud.navlinks.profile')." ".__('crud.general.updated'));
    }

    public function uploadVerificationDocument(StoreVerificationDocumentsRequest $request)
    {
        $credentials = $request->validated()['document']; // submitted Documents
        
        if(!$credentials) {
            // No Document Submitted
            return redirect(route('provider.settings')."#verification")
                ->withErrors("No Document Found");
        }
        $keys = array_keys($credentials); // These will be the document id's

        // all the required documents
        $required = Arr::flatten(Document::select('id')->where('status', '1')->get()->toArray());
        
        // Submitted By Provider
        $given = Arr::flatten(ProviderDocument::select('document_id')->where('provider_id', auth()->user('provider')->id)->get()->toArray());

        $providerDocument['provider_id'] = auth()->user('provider')->id;
        $providerDocument['status'] = 'ASSESSING';

        // Not Submitted
        $to_submit = array_diff($required, $given);

        foreach($credentials as $key => $value) {
            // Iterate Through Each Document
            if(in_array($key, $to_submit)) {
                $providerDocument['document_id'] = $key;
                $providerDocument['url'] = $value->store('public/provider/document');
                ProviderDocument::create($providerDocument);
            }
        }

        return redirect(route('provider.settings')."#verification")
            ->withSuccess("Document Uploaded Successfully");
    }
}