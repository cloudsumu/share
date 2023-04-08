<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

use App\Http\Requests\Admin\{
    StoreFaqRequest, UpdateFaqRequest
};

class FaqController extends Controller
{
    public function __construct()
    {
        $this->middleware('demo')->only(['update', 'destroy']);
    }
    
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('list faqs', Faq::class);

        $search = $request->get('search', '');

        $faqs = Faq::search($search)
            ->latest()
            ->paginate();

        return view('admin.faq.index', compact('faqs'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create faqs', Faq::class);
        return view('admin.faq.create');
    }

    /**
     * @param \App\Http\Requests\FaqStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreFaqRequest $request)
    {
        $validated = $request->validated();
        Faq::create($validated);

        return redirect()
            ->route('admin.faq.index')
            ->withSuccess(__('crud.general.created'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Faq $faq
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Faq $faq)
    {
        abort(403);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Faq $faq
     * @return \Illuminate\Http\Response
     */
    public function edit(Faq $faq)
    {
        $this->authorize('update faqs', $faq);
        return view('admin.faq.edit', compact('faq'));
    }

    /**
     * @param \App\Http\Requests\UpdateFaqRequest $request
     * @param \App\Models\Faq $faq
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFaqRequest $request, Faq $faq)
    {
        $validated = $request->validated();
        $faq->update($validated);

        return redirect()
            ->route('admin.faq.index')
            ->withSuccess(__('crud.general.updated'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Faq $faq
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Faq $faq)
    {
        $this->authorize('delete faqs', $faq);
        try {
            $faq->delete();
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
