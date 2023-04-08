<?php

namespace App\Http\Controllers\Admin;

use App\Models\Document;
use App\Models\Provider;
use App\Models\ProviderDocument;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ProviderDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('demo')->only(['update', 'destroy']);
    }
    
    public function acceptDocument(ProviderDocument $providerDocument)
    {
        $credentials['status'] = "ACTIVE";
        $providerDocument->update($credentials);

        if($this->allDocumentsSubmitted($providerDocument->provider)) {
            $credentials = [];
            $credentials['verified'] = '1';
            $providerDocument->provider->update($credentials);

            $providerCredentials['status'] = 'approved';
            $providerCredentials['verified'] = '1';
            $providerDocument->provider->update($providerCredentials);
    
            return redirect()
                ->back()
                ->withSuccess(__('crud.admin.providers.name')." & ".__('crud.admin.documents.name')." ".__('crud.general.approved'));

        }

        return redirect()
            ->back()
            ->withSuccess('Document Accepted Successfully.');
    }

    public function rejectDocument(ProviderDocument $providerDocument)
    {
        // Add Notification Here. Tell Provider that his document was rejected.
        $url = $providerDocument->url;
        Storage::delete($url);
        
        $providerDocument->delete();

        $credentials['verified'] = '0';
        $credentials['status'] = 'document';
        $providerDocument->provider->update($credentials);

        return redirect()
            ->back()
            ->withSuccess('Document Removed Successfully.');
    }

    public function allDocumentsSubmitted(Provider $provider) : bool
    {
        $requiredIds = Document::where('status', '1')->count();
        $given = $provider->documents()->where('status', 'ACTIVE')->count();
        return ($requiredIds == $given) ? true : false;
    }
}
