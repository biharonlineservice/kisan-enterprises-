<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Crop;
use App\Models\CropVariety;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CropController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->input('tab', 'all');

        $query = Crop::withCount('varieties')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('hsn_code', 'like', $term);
                });
            });

        if ($tab === 'active') {
            $query->where('is_active', true);
        } elseif ($tab === 'inactive') {
            $query->where('is_active', false);
        } elseif ($tab === 'rates_live') {
            $query->where('show_rate_on_website', true)
                  ->whereNotNull('current_purchase_rate')
                  ->where('current_purchase_rate', '>', 0);
        } elseif ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $crops = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $metrics = [
            'totalCrops' => Crop::count(),
            'activeCrops' => Crop::where('is_active', true)->count(),
            'inactiveCrops' => Crop::where('is_active', false)->count(),
            'totalVarieties' => CropVariety::count(),
            'ratesLive' => Crop::where('show_rate_on_website', true)
                ->whereNotNull('current_purchase_rate')
                ->where('current_purchase_rate', '>', 0)
                ->count(),
        ];

        return view('masters.crops.index', compact('crops', 'metrics', 'tab'));
    }

    public function export(Request $request): StreamedResponse
    {
        $tab = $request->input('tab', 'all');

        $query = Crop::withCount('varieties')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                        ->orWhere('hsn_code', 'like', $term);
                });
            });

        if ($tab === 'active') {
            $query->where('is_active', true);
        } elseif ($tab === 'inactive') {
            $query->where('is_active', false);
        } elseif ($tab === 'rates_live') {
            $query->where('show_rate_on_website', true)
                  ->whereNotNull('current_purchase_rate')
                  ->where('current_purchase_rate', '>', 0);
        } elseif ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $crops = $query->orderBy('name')->get();
        $filename = 'crops-master-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($crops) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($handle, [
                'ID',
                'Crop Name',
                'Crop Code',
                'Standard Unit',
                'Current Buying Rate (₹)',
                'Max Buying Rate (₹)',
                'Rate Note',
                'Website Hero Live',
                'HSN Code',
                'Varieties Count',
                'Status',
            ]);

            foreach ($crops as $crop) {
                fputcsv($handle, [
                    $crop->id,
                    $crop->name,
                    $crop->code,
                    ucfirst($crop->unit),
                    $crop->current_purchase_rate ? number_format((float) $crop->current_purchase_rate, 2, '.', '') : '0.00',
                    $crop->max_purchase_rate ? number_format((float) $crop->max_purchase_rate, 2, '.', '') : '',
                    $crop->rate_note ?? '',
                    $crop->show_rate_on_website ? 'Yes' : 'No',
                    $crop->hsn_code ?? '',
                    $crop->varieties_count,
                    $crop->is_active ? 'Active' : 'Inactive',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(): View
    {
        return view('masters.crops.form', [
            'crop' => new Crop(['unit' => 'quintal', 'is_active' => true, 'show_rate_on_website' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:20', 'unique:crops,code'],
            'unit' => ['required', 'string', 'max:20'],
            'hsn_code' => ['nullable', 'string', 'max:20'],
            'current_purchase_rate' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'max_purchase_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    if ($value !== null && $value !== '' && $request->filled('current_purchase_rate') && (float) $value < (float) $request->input('current_purchase_rate')) {
                        $fail('Maximum purchase rate must be greater than or equal to the base purchase rate.');
                    }
                },
            ],
            'rate_note' => ['nullable', 'string', 'max:120'],
            'show_rate_on_website' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($data['code'])) {
            $data['code'] = strtoupper(Str::slug(substr($data['name'], 0, 10), ''));
            $originalCode = $data['code'];
            $i = 1;
            while (Crop::where('code', $data['code'])->exists()) {
                $data['code'] = $originalCode . $i++;
            }
        } else {
            $data['code'] = strtoupper(trim($data['code']));
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['show_rate_on_website'] = $request->has('rate_section_present')
            ? $request->boolean('show_rate_on_website')
            : ($request->has('show_rate_on_website') ? $request->boolean('show_rate_on_website') : true);

        if (! empty($data['current_purchase_rate']) && (float) $data['current_purchase_rate'] > 0) {
            $data['rate_updated_at'] = now();
        }

        $crop = Crop::create($data);

        AuditLogger::log('crop.created', $crop, [], $crop->toArray(), "Created crop {$crop->name}");

        return redirect()->route('crops.show', $crop)->with('success', "Crop '{$crop->name}' created. You can now add its varieties.");
    }

    public function show(Crop $crop): View
    {
        $crop->load(['varieties' => fn ($q) => $q->orderBy('name')]);

        return view('masters.crops.show', compact('crop'));
    }

    public function edit(Crop $crop): View
    {
        return view('masters.crops.form', compact('crop'));
    }

    public function update(Request $request, Crop $crop): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', Rule::unique('crops', 'code')->ignore($crop->id)],
            'unit' => ['required', 'string', 'max:20'],
            'hsn_code' => ['nullable', 'string', 'max:20'],
            'current_purchase_rate' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'max_purchase_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    if ($value !== null && $value !== '' && $request->filled('current_purchase_rate') && (float) $value < (float) $request->input('current_purchase_rate')) {
                        $fail('Maximum purchase rate must be greater than or equal to the base purchase rate.');
                    }
                },
            ],
            'rate_note' => ['nullable', 'string', 'max:120'],
            'show_rate_on_website' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active');

        if ($request->has('rate_section_present') || $request->has('show_rate_on_website')) {
            $data['show_rate_on_website'] = $request->boolean('show_rate_on_website');
        } else {
            unset($data['show_rate_on_website']);
        }

        if (! $request->has('rate_section_present') && ! $request->hasAny(['current_purchase_rate', 'max_purchase_rate', 'rate_note'])) {
            unset($data['current_purchase_rate'], $data['max_purchase_rate'], $data['rate_note']);
        } else {
            $rateChanged = (float) ($data['current_purchase_rate'] ?? 0) !== (float) ($crop->current_purchase_rate ?? 0)
                || (float) ($data['max_purchase_rate'] ?? 0) !== (float) ($crop->max_purchase_rate ?? 0)
                || ($data['rate_note'] ?? null) !== $crop->rate_note;

            if ($rateChanged && ! empty($data['current_purchase_rate']) && (float) $data['current_purchase_rate'] > 0) {
                $data['rate_updated_at'] = now();
            }
        }

        $original = $crop->getOriginal();
        $crop->update($data);

        AuditLogger::changes('crop.updated', $crop, $original, "Updated crop {$crop->name}");

        return redirect()->route('crops.show', $crop)->with('success', "Crop '{$crop->name}' updated.");
    }

    public function toggle(Crop $crop): RedirectResponse
    {
        $crop->update(['is_active' => ! $crop->is_active]);
        AuditLogger::log($crop->is_active ? 'crop.activated' : 'crop.deactivated', $crop);

        return back()->with('success', "Crop '{$crop->name}' " . ($crop->is_active ? 'activated' : 'deactivated') . '.');
    }

    // Variety management under a crop
    public function storeVariety(Request $request, Crop $crop): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('crop_varieties', 'name')->where('crop_id', $crop->id)],
            'code' => ['nullable', 'string', 'max:30'],
            'standard_moisture_pct' => ['required', 'numeric', 'between:0,100'],
            'default_grade' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $variety = $crop->varieties()->create([
            ...$data,
            'is_active' => true,
        ]);

        AuditLogger::log('crop_variety.created', $variety, [], $variety->toArray(), "Added variety {$variety->name} to {$crop->name}");

        return back()->with('success', "Variety '{$variety->name}' added to {$crop->name}.");
    }

    public function updateVariety(Request $request, Crop $crop, CropVariety $variety): RedirectResponse
    {
        abort_unless($variety->crop_id === $crop->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('crop_varieties', 'name')->where('crop_id', $crop->id)->ignore($variety->id)],
            'code' => ['nullable', 'string', 'max:30'],
            'standard_moisture_pct' => ['required', 'numeric', 'between:0,100'],
            'default_grade' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $original = $variety->getOriginal();
        $variety->update($data);

        AuditLogger::changes('crop_variety.updated', $variety, $original, "Updated variety {$variety->name}");

        return back()->with('success', "Variety '{$variety->name}' updated.");
    }
}
