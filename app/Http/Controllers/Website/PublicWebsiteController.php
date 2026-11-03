<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Mail\ContactInquiryAdminMail;
use App\Models\Crop;
use App\Models\Farmer;
use App\Models\Warehouse;
use App\Models\WebsiteContent;
use App\Models\WebsiteInquiry;
use App\Services\CropPriceTrendService;
use App\Services\DocumentNumber;
use App\Services\MailConfigService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class PublicWebsiteController extends Controller
{
    public function home(CropPriceTrendService $trendService): View
    {
        $crops = Crop::with('varieties')->where('is_active', true)->orderBy('name')->get();
        $rateCrops = $crops
            ->where('show_rate_on_website', true)
            ->filter(fn (Crop $c) => $c->current_purchase_rate !== null && (float) $c->current_purchase_rate > 0)
            ->values();
        $latestRateUpdate = $rateCrops->max('rate_updated_at');
        $priceTrends = collect($trendService->build($rateCrops))->keyBy('id');
        $warehouses = Warehouse::where('is_active', true)
            ->where('code', '!=', 'WH-DIRECT')
            ->orderBy('code')
            ->get();

        return view('website.home', compact('crops', 'rateCrops', 'latestRateUpdate', 'priceTrends', 'warehouses'));
    }

    public function about(): View
    {
        $crops = Crop::withCount('varieties')->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)
            ->where('code', '!=', 'WH-DIRECT')
            ->orderBy('code')
            ->get();
        $totalCapacityQtl = $warehouses->sum(fn (Warehouse $w) => (float) ($w->capacity_quintals ?? 0));
        $varietiesCount = $crops->sum('varieties_count');

        return view('website.about', compact('crops', 'warehouses', 'totalCapacityQtl', 'varietiesCount'));
    }

    public function procurement(CropPriceTrendService $trendService): View
    {
        $crops = Crop::with('varieties')->where('is_active', true)->orderBy('name')->get();
        $rateCrops = $crops
            ->where('show_rate_on_website', true)
            ->filter(fn (Crop $c) => $c->current_purchase_rate !== null && (float) $c->current_purchase_rate > 0)
            ->values();
        $latestRateUpdate = $rateCrops->max('rate_updated_at');
        $priceTrends = collect($trendService->build($rateCrops))->keyBy('id');

        return view('website.procurement', compact('crops', 'rateCrops', 'latestRateUpdate', 'priceTrends'));
    }

    public function products(): View
    {
        $crops = Crop::with('varieties')->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::where('is_active', true)
            ->where('code', '!=', 'WH-DIRECT')
            ->orderBy('code')
            ->get();
        $totalCapacityQtl = $warehouses->sum(fn (Warehouse $w) => (float) ($w->capacity_quintals ?? 0));
        $varietiesCount = $crops->sum(fn (Crop $c) => $c->varieties->count());

        return view('website.products', compact('crops', 'warehouses', 'totalCapacityQtl', 'varietiesCount'));
    }

    public function farmerRegister(): View
    {
        $crops = Crop::where('is_active', true)->orderBy('name')->get();

        return view('website.farmer_register', compact('crops'));
    }

    public function farmerRegisterStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'father_name' => ['nullable', 'string', 'max:150'],
            'mobile' => ['required', 'regex:/^[6-9]\d{9}$/'],
            'alt_mobile' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
            'village' => ['required', 'string', 'max:100'],
            'post_office' => ['nullable', 'string', 'max:100'],
            'tehsil' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['nullable', 'digits:6'],
            'aadhaar_number' => ['nullable', 'digits:12'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_ifsc' => ['nullable', 'string', 'max:11'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'crops_grown' => ['nullable', 'string', 'max:255'],
            'estimated_acreage' => ['nullable', 'numeric', 'min:0'],
        ]);

        $farmerId = DocumentNumber::next('farmer');

        $farmer = Farmer::create([
            'farmer_id' => $farmerId,
            'name' => $data['name'],
            'father_name' => $data['father_name'] ?? null,
            'mobile' => $data['mobile'],
            'alt_mobile' => $data['alt_mobile'] ?? null,
            'village' => $data['village'],
            'post_office' => $data['post_office'] ?? null,
            'tehsil' => $data['tehsil'],
            'district' => $data['district'],
            'state' => $data['state'],
            'pincode' => $data['pincode'] ?? null,
            'aadhaar_number' => $data['aadhaar_number'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'bank_ifsc' => $data['bank_ifsc'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('website.farmer.register')
            ->with('success', "Registration successful! Welcome to Kisan Enterprises. Your Farmer ID is {$farmer->farmer_id}. Please keep this ID handy when bringing your harvest to our weighbridge.");
    }

    public function contact(): View
    {
        $warehouses = Warehouse::where('is_active', true)
            ->where('code', '!=', 'WH-DIRECT')
            ->orderBy('code')
            ->get();
        $crops = Crop::where('is_active', true)->orderBy('name')->get();

        return view('website.contact', compact('warehouses', 'crops'));
    }

    public function inquiryStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'inquiry_type' => ['required', 'in:general,bulk_buyer,farmer'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $inquiry = WebsiteInquiry::create($data);

        try {
            $businessEmail = app(SettingsService::class)->get('business.email') ?: config('mail.from.address');
            if (! empty($businessEmail) && filter_var($businessEmail, FILTER_VALIDATE_EMAIL)) {
                MailConfigService::apply();
                Mail::to($businessEmail)->send(new ContactInquiryAdminMail($inquiry));
            }
        } catch (Throwable $e) {
            report($e);
        }

        $msg = app()->getLocale() === 'hi'
            ? 'धन्यवाद! आपका संदेश सफलतापूर्वक प्राप्त हो गया है। हमारी कृषि व्यापार डेस्क शीघ्र ही आपसे संपर्क करेगी।'
            : 'Thank you! Your message has been received. Our agricultural trading desk will get back to you shortly.';

        return redirect()->route('website.contact')->with('success', $msg);
    }

    public function privacy(): View
    {
        return view('website.privacy');
    }

    public function terms(): View
    {
        return view('website.terms');
    }
}
