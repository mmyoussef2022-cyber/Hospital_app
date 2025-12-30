<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandingPageSetting;
use App\Models\LandingPageOffer;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class LandingPageAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:landing-page.manage');
    }

    /**
     * Display the landing page management dashboard
     */
    public function dashboard()
    {
        $settings = LandingPageSetting::getInstance();
        
        $statistics = [
            'total_offers' => LandingPageOffer::count(),
            'active_offers' => LandingPageOffer::where('is_active', true)->count(),
            'featured_offers' => LandingPageOffer::where('is_featured', true)->count(),
            'expired_offers' => LandingPageOffer::where('valid_until', '<', now())->count(),
        ];

        return view('admin.landing-page.dashboard', compact('settings', 'statistics'));
    }

    /**
     * Show the form for editing landing page settings
     */
    public function settings()
    {
        $settings = LandingPageSetting::getInstance();
        $departments = Department::where('is_active', true)->get();
        
        return view('admin.landing-page.settings', compact('settings', 'departments'));
    }

    /**
     * Update landing page settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'hospital_name' => 'required|string|max:255',
            'hospital_tagline' => 'nullable|string|max:500',
            'hospital_description' => 'nullable|string|max:2000',
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_cta_primary_text' => 'nullable|string|max:100',
            'hero_cta_secondary_text' => 'nullable|string|max:100',
            'about_title' => 'nullable|string|max:255',
            'about_content' => 'nullable|string|max:5000',
            'services_title' => 'nullable|string|max:255',
            'services_subtitle' => 'nullable|string|max:500',
            'doctors_title' => 'nullable|string|max:255',
            'doctors_subtitle' => 'nullable|string|max:500',
            'featured_doctors_count' => 'nullable|integer|min:1|max:20',
            'offers_title' => 'nullable|string|max:255',
            'offers_subtitle' => 'nullable|string|max:500',
            'phone_primary' => 'nullable|string|max:20',
            'phone_emergency' => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'email_primary' => 'nullable|email|max:255',
            'email_appointments' => 'nullable|email|max:255',
            'address_text' => 'nullable|string|max:1000',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:1000',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'accent_color' => 'nullable|string|max:7',
        ]);

        $settings = LandingPageSetting::first() ?? new LandingPageSetting();
        
        $data = $request->except(['hero_background_image', 'hospital_logo']);
        
        // Handle boolean fields
        $booleanFields = [
            'hero_section_enabled',
            'about_section_enabled',
            'services_section_enabled',
            'doctors_section_enabled',
            'offers_section_enabled',
            'schedule_section_enabled',
            'location_section_enabled'
        ];
        
        foreach ($booleanFields as $field) {
            $data[$field] = $request->has($field);
        }

        // Handle file uploads
        if ($request->hasFile('hospital_logo')) {
            if ($settings->hospital_logo) {
                Storage::disk('public')->delete($settings->hospital_logo);
            }
            $data['hospital_logo'] = $request->file('hospital_logo')->store('landing-page/logos', 'public');
        }

        if ($request->hasFile('hero_background_image')) {
            if ($settings->hero_background_image) {
                Storage::disk('public')->delete($settings->hero_background_image);
            }
            $data['hero_background_image'] = $request->file('hero_background_image')->store('landing-page/hero', 'public');
        }

        // Handle working hours
        if ($request->has('working_hours')) {
            $data['working_hours'] = $request->working_hours;
        }

        $settings->fill($data);
        $settings->save();

        return redirect()->route('admin.landing-page.settings')
            ->with('success', 'تم تحديث إعدادات صفحة الهبوط بنجاح');
    }

    /**
     * Display offers management
     */
    public function offers()
    {
        $offers = LandingPageOffer::orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.landing-page.offers.index', compact('offers'));
    }

    /**
     * Show the form for creating a new offer
     */
    public function createOffer()
    {
        return view('admin.landing-page.offers.create');
    }

    /**
     * Store a newly created offer
     */
    public function storeOffer(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'discount_type' => 'required|in:percentage,fixed,free',
            'discount_value' => 'required_unless:discount_type,free|numeric|min:0',
            'discount_badge_text' => 'nullable|string|max:100',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|url|max:255',
            'terms_conditions' => 'nullable|string|max:2000',
            'max_uses' => 'nullable|integer|min:1',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('image');
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['current_uses'] = 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('landing-page/offers', 'public');
        }

        LandingPageOffer::create($data);

        return redirect()->route('admin.landing-page.offers')
            ->with('success', 'تم إنشاء العرض بنجاح');
    }

    /**
     * Show the form for editing an offer
     */
    public function editOffer(LandingPageOffer $offer)
    {
        return view('admin.landing-page.offers.edit', compact('offer'));
    }

    /**
     * Update the specified offer
     */
    public function updateOffer(Request $request, LandingPageOffer $offer)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'discount_type' => 'required|in:percentage,fixed,free',
            'discount_value' => 'required_unless:discount_type,free|numeric|min:0',
            'discount_badge_text' => 'nullable|string|max:100',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|url|max:255',
            'terms_conditions' => 'nullable|string|max:2000',
            'max_uses' => 'nullable|integer|min:1',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('image');
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('image')) {
            if ($offer->image) {
                Storage::disk('public')->delete($offer->image);
            }
            $data['image'] = $request->file('image')->store('landing-page/offers', 'public');
        }

        $offer->update($data);

        return redirect()->route('admin.landing-page.offers')
            ->with('success', 'تم تحديث العرض بنجاح');
    }

    /**
     * Remove the specified offer
     */
    public function destroyOffer(LandingPageOffer $offer)
    {
        if ($offer->image) {
            Storage::disk('public')->delete($offer->image);
        }

        $offer->delete();

        return redirect()->route('admin.landing-page.offers')
            ->with('success', 'تم حذف العرض بنجاح');
    }

    /**
     * Toggle offer status
     */
    public function toggleOfferStatus(LandingPageOffer $offer)
    {
        $offer->update(['is_active' => !$offer->is_active]);

        $status = $offer->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';
        
        return response()->json([
            'success' => true,
            'message' => $status . ' العرض بنجاح',
            'is_active' => $offer->is_active
        ]);
    }

    /**
     * Preview landing page
     */
    public function preview()
    {
        $settings = LandingPageSetting::getInstance();
        
        // Get featured doctors
        $featuredDoctors = Doctor::where('is_active', true)
            ->limit($settings->featured_doctors_count ?? 6)
            ->get();

        // Get active offers
        $offers = LandingPageOffer::where('is_active', true)
            ->where('valid_until', '>=', now())
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        // Get departments
        $departments = Department::where('is_active', true)->limit(8)->get();

        return view('admin.landing-page.preview', compact(
            'settings',
            'featuredDoctors',
            'offers',
            'departments'
        ));
    }

    /**
     * Clear landing page cache
     */
    public function clearCache()
    {
        Cache::forget('landing_page_settings');
        
        return response()->json([
            'success' => true,
            'message' => 'تم مسح ذاكرة التخزين المؤقت بنجاح'
        ]);
    }

    /**
     * Get landing page analytics
     */
    public function analytics()
    {
        // This would typically integrate with Google Analytics or similar
        $analytics = [
            'page_views' => 1250,
            'unique_visitors' => 890,
            'bounce_rate' => 35.2,
            'avg_session_duration' => '2:45',
            'conversion_rate' => 4.8,
            'top_pages' => [
                ['page' => '/', 'views' => 650],
                ['page' => '/doctors', 'views' => 320],
                ['page' => '/services', 'views' => 180],
                ['page' => '/booking', 'views' => 100],
            ]
        ];

        return view('admin.landing-page.analytics', compact('analytics'));
    }
}