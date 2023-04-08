<?php

namespace App\Http\Controllers\Admin;

use App\Models\PeakHour;
use App\Models\GeoFencing;
use App\Models\ServiceType;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Admin\StoreServiceTypeRequest;
use App\Http\Requests\Admin\UpdateServiceTypeRequest;

class ServiceTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('demo')->only(['update', 'destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('list serviceTypes', ServiceType::class);
        $search = $request->get('search' ,'');
        $serviceTypes = ServiceType::search($search)
            ->latest()
            ->paginate();

        return view('admin.serviceType.index', compact('serviceTypes', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create serviceTypes', ServiceType::class);
        $serviceTypes = ServiceType::all();
        return view('admin.serviceType.create', compact('serviceTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServiceTypeRequest $request)
    {
        $credentials = $request->validated();

        if ($request->hasFile('image')) {
            $credentials['image'] = $request->file('image')->store('public/service/image');
        }

        if ($request->hasFile('marker')) {
            $credentials['marker'] = $request->file('marker')->store('public/service/marker');
        }

        ServiceType::create($credentials);

        return redirect()
            ->route('admin.serviceType.index')
            ->withSuccess(__('crud.general.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(403);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ServiceType $serviceType)
    {
        $geoFences = GeoFencing::all();
        $peakHours = PeakHour::all();
        $this->authorize('view serviceTypes', $serviceType);
        return view('admin.serviceType.edit', compact('serviceType', 'peakHours', 'geoFences'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateServiceTypeRequest $request, ServiceType $serviceType)
    {
        $credentials = $request->validated();

        if ($request->hasFile('image')) {
            if($serviceType->image) {
                Storage::delete($serviceType->image);
            }
            $credentials['image'] = $request->file('image')->store('public/service/image');
        }
        
        if ($request->hasFile('marker')) {
            if($serviceType->marker) {
                Storage::delete($serviceType->marker);
            }
            $credentials['marker'] = $request->file('marker')->store('public/service/marker');
        }

        $serviceType->update($credentials);

        if($serviceType->parent) {
            return redirect()
                ->route('admin.subServices', $serviceType->parent->id)
                ->withSuccess(__('crud.general.updated'));
        }

        return redirect()
            ->route('admin.serviceType.index')
            ->withSuccess(__('crud.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServiceType $serviceType)
    {
        $this->authorize('delete serviceTypes', $serviceType);
        try {
            $serviceType->delete();
        } catch(\Illuminate\Database\QueryException $e) {
            if($e->getCode() == 23000)
            {
                return redirect()
                    ->back()
                    ->withErrors(__('crud.general.integrity_violation'));
            }
            return redirect()
                ->back()
                ->withErrors(__('crud.general.not_done'));
        }
        return redirect()
            ->back()
            ->withSuccess(__('crud.general.deleted'));
    }

    public function subServices(ServiceType $serviceType)
    {
        return view('admin.serviceType.subServices', compact('serviceType'));
    }
}
