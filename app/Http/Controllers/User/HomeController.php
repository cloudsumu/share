<?php

namespace App\Http\Controllers\User;

use App\Models\Faq;
use App\Models\Blog;
use App\Models\Setting;
use App\Models\GeoFencing;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * Get the user home.
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $geoFences = GeoFencing::all();
        $results = Setting::select('key', 'value')->where('key', 'like', 'home_%')->get()->toArray();
        $settings = [];
        foreach($results as $result) {
            $settings[$result['key']] = $result['value'];
        }
        return view('index', compact('settings'));
    }

    /**
     * Get the FAQ's page.
     * 
     * @return \Illuminate\Http\Response
     */
    public function faq()
    {
        $faqs = Faq::latest()->where('status', '1')->paginate();
        return view('faq', compact('faqs'));
    }

    /**
     * Get the User Dashboard.
     * 
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        return view('user.dashboard');
    }

    /**
     * Get the Contact Us.
     * 
     * @return \Illuminate\Http\Response
     */
    public function contactUs()
    {
        return view('contactUs');
    }

    /**
     * Get the Terms & Conditions.
     * 
     * @return \Illuminate\Http\Response
     */
    public function tnc()
    {
        return view('tnc');
    }

    /**
     * Get the Privacy Policy.
     * 
     * @return \Illuminate\Http\Response
     */
    public function privacy()
    {
        return view('privacy');
    }

    public function blog()
    {
        $blogs = Blog::all();

        return view('blog', compact('blogs'));
    }

    public function showBlog($blog)
    {
        // Type Hint this
        return view('viewBlog', compact('blog'));
    }

    public function service(GeoFencing $geoFencing, ServiceType $serviceType)
    {
        $subServices = $geoFencing->serviceType()->wherePivot('status', '1')->get();
        return view('service', compact('serviceType', 'geoFencing', 'subServices'));
    }

    public function getServiceAjax(Request $request, ServiceType $serviceType)
    {
        if (!$request->ajax()) {
            abort(404);
        }
        
        return json_encode($serviceType);

    }
}