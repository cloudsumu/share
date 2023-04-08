<?php

namespace App\Http\Controllers\Admin;

use App\Models\GeoFencing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGeoFencingRequest;
use App\Http\Requests\Admin\UpdateGeoFencingRequest;

class GeoFencingController extends Controller
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
        $this->authorize('list geoFences', GeoFencing::class);
        $search = $request->get('search', '');

        $geoFencings = GeoFencing::search($search)
            ->latest()
            ->paginate();
        return view('admin.geoFencing.index', compact('geoFencings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create geoFences', GeoFencing::class);
        return view('admin.geoFencing.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGeoFencingRequest $request)
    {
        $credentials = $request->all();
        GeoFencing::create($credentials);

        return redirect()
            ->route('admin.geoFencing.index')
            ->with('success', __('crud.admin.geoFencings.name')." ".__('crud.general.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(GeoFencing $geoFencing)
    {
        $this->authorize('view geoFences', $geoFencing);
        return view('admin.geoFencing.edit', compact('geoFencing'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGeoFencingRequest $request, GeoFencing $geoFencing)
    {
        $credentials = $request->all();
        $geoFencing->update($credentials);

        return redirect()
            ->route('admin.geoFencing.index')
            ->with('success', __('crud.admin.geoFencings.name')." ".__('crud.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(GeoFencing $geoFencing)
    {
        $this->authorize('delete geoFences', $geoFencing);
        try {
            $geoFencing->delete();
        } catch (\Illuminate\Database\QueryException $e) {
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
}
