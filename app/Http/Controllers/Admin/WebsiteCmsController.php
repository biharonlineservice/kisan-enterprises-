<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\WebsiteContent;
use App\Models\WebsiteInquiry;
use App\Services\AuditLogger;
use App\Services\CropPriceTrendService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteCmsController extends Controller
{
    public function index(Request $request, CropPriceTrendService $trendService): View
    {
        $tab = $request->input('tab', 'rates');
        if (!in_array($tab, ['rates', 'cms', 'inquiries', 'all'], true)) {
            $tab = 'rates';
        }

        $inquiries = WebsiteInquiry::latest()->take(10)->get();
        $crops = Crop::where('is_active', true)->orderBy('name')->get();
        $ratedCrops = $crops->filter(fn (Crop $c) => $c->current_purchase_rate !== null && (float) $c->current_purchase_rate > 0)->values();
        $priceTrends = collect($trendService->build($ratedCrops))->keyBy('id');

        $metrics = [
            'ratesLive' => $crops->where('show_rate_on_website', true)->count(),
            'totalCrops' => $crops->count(),
            'totalInquiries' => WebsiteInquiry::count(),
            'newInquiries' => WebsiteInquiry::where('status', WebsiteInquiry::STATUS_NEW)->count(),
        ];

        return view('admin.website.index', compact('inquiries', 'crops', 'priceTrends', 'metrics', 'tab'));
    }

    public function update(Request $request): RedirectResponse
    {
        $fields = $request->except(['_token', '_form_token', '_method', 'rates', 'tab']);

        foreach ($fields as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            WebsiteContent::set($key, 'general', null, (string) $value);
        }

        return redirect()->route('website.admin.index', ['tab' => 'cms'])->with('success', 'Website content updated successfully.');
    }

    public function updateCropRates(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rates' => ['required', 'array'],
            'rates.*.current_purchase_rate' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'rates.*.max_purchase_rate' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'rates.*.rate_note' => ['nullable', 'string', 'max:120'],
            'rates.*.show_rate_on_website' => ['nullable', 'boolean'],
        ]);

        foreach ($validated['rates'] as $cropId => $row) {
            $minVal = $row['current_purchase_rate'] ?? null;
            $maxVal = $row['max_purchase_rate'] ?? null;
            if ($minVal !== null && $minVal !== '' && $maxVal !== null && $maxVal !== '' && (float) $maxVal > 0 && (float) $maxVal < (float) $minVal) {
                return back()->withInput()->withErrors([
                    "rates.{$cropId}.max_purchase_rate" => 'Maximum rate must be greater than or equal to the base buying rate.',
                ]);
            }
        }

        $now = now();
        $updatedCount = 0;

        foreach ($validated['rates'] as $cropId => $row) {
            $crop = Crop::find($cropId);
            if (! $crop) {
                continue;
            }

            $minRate = ($row['current_purchase_rate'] ?? null) !== null && $row['current_purchase_rate'] !== ''
                ? round((float) $row['current_purchase_rate'], 2)
                : null;
            $maxRate = ($row['max_purchase_rate'] ?? null) !== null && $row['max_purchase_rate'] !== '' && (float) $row['max_purchase_rate'] > 0
                ? round((float) $row['max_purchase_rate'], 2)
                : null;
            $note = isset($row['rate_note']) && trim((string) $row['rate_note']) !== ''
                ? trim((string) $row['rate_note'])
                : null;
            $showOnWebsite = ! empty($row['show_rate_on_website']);

            $crop->update([
                'current_purchase_rate' => $minRate,
                'max_purchase_rate' => $maxRate,
                'rate_note' => $note,
                'show_rate_on_website' => $showOnWebsite,
                'rate_updated_at' => $minRate !== null && $minRate > 0 ? $now : $crop->rate_updated_at,
            ]);
            $updatedCount++;
        }

        AuditLogger::log(
            'website.crop_rates_updated',
            null,
            [],
            ['updated_crops' => $updatedCount],
            "Updated live crop purchase prices for {$updatedCount} crop(s) on website Hero section"
        );

        return redirect()->route('website.admin.index', ['tab' => 'rates'])->with('success', 'Today\'s live crop purchase prices updated on the website Hero section.');
    }

    public function inquiries(Request $request): View
    {
        $inquiries = WebsiteInquiry::when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('inquiry_type', $request->type))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.website.inquiries', compact('inquiries'));
    }

    public function toggleInquiryStatus(WebsiteInquiry $websiteInquiry): RedirectResponse
    {
        $newStatus = $websiteInquiry->status === WebsiteInquiry::STATUS_NEW
            ? WebsiteInquiry::STATUS_RESPONDED
            : WebsiteInquiry::STATUS_NEW;

        $websiteInquiry->update(['status' => $newStatus]);

        return back()->with('success', 'Inquiry status updated.');
    }
}
