<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestSmtpMail;
use App\Services\AuditLogger;
use App\Services\FinancialYear;
use App\Services\MailConfigService;
use App\Services\SettingsService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public const TABS = [
        'business' => 'Business Profile',
        'documents' => 'Invoices & Receipts',
        'numbering' => 'Document Numbering',
        'system' => 'Regional & Security',
        'mail' => 'Email & SMTP',
        'whatsapp' => 'WhatsApp API',
    ];

    public function __construct(private SettingsService $settings) {}

    public function edit(Request $request): View
    {
        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'business';
        $s = $this->settings->all();
        $currentFy = FinancialYear::label();

        $metrics = [
            'currentFy' => $currentFy,
            'businessName' => $s['business.name'] ?? 'Kisan Enterprises',
            'gstin' => $s['business.gstin'] ?? null,
            'taxEnabled' => !empty($s['tax.enabled']) && $s['tax.enabled'] === '1',
            'taxRate' => $s['tax.default_rate'] ?? '0.00',
            'taxLabel' => $s['tax.label'] ?? 'GST',
            'padding' => (int) ($s['numbering.padding'] ?? 5),
            'sessionTimeout' => (int) ($s['security.session_timeout_minutes'] ?? 30),
            'delayedBonusEnabled' => !empty($s['procurement.delayed_bonus_enabled']) && $s['procurement.delayed_bonus_enabled'] === '1',
            'mailDriver' => $s['mail.mailer'] ?? 'log',
            'mailConfigured' => !empty($s['mail.host']) && !empty($s['mail.username']),
            'whatsappEnabled' => !empty($s['whatsapp.enabled']) && in_array((string) $s['whatsapp.enabled'], ['1', 'true', 'on'], true),
            'whatsappProvider' => $s['whatsapp.provider'] ?? 'log',
        ];

        return view('admin.settings.edit', [
            'tab' => $tab,
            'tabs' => self::TABS,
            's' => $s,
            'currentFy' => $currentFy,
            'metrics' => $metrics,
        ]);
    }

    public function update(Request $request, string $tab): RedirectResponse
    {
        abort_unless(array_key_exists($tab, self::TABS), 404);

        $values = match ($tab) {
            'business' => $this->business($request),
            'documents' => $this->documents($request),
            'numbering' => $this->numbering($request),
            'system' => $this->system($request),
            'mail' => $this->mail($request),
            'whatsapp' => $this->whatsapp($request),
        };

        [$old, $new] = $this->settings->setMany($values, $request->user()->id);

        if ($new) {
            AuditLogger::log('settings.updated', null, $old, $new, self::TABS[$tab].' settings changed');
        }

        return redirect()->route('settings.edit', ['tab' => $tab])
            ->with('success', $new ? self::TABS[$tab].' saved.' : 'No changes to save.');
    }

    private function business(Request $request): array
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'business_tagline' => ['nullable', 'string', 'max:150'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'business_state' => ['nullable', 'string', 'max:60'],
            'business_phone' => ['nullable', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'business_alt_phone' => ['nullable', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'business_email' => ['nullable', 'email', 'max:150'],
            'business_gstin' => ['nullable', 'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/'],
            'business_pan' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
            'business_bank_name' => ['nullable', 'string', 'max:120'],
            'business_account_name' => ['nullable', 'string', 'max:150'],
            'business_account_number' => ['nullable', 'string', 'max:50'],
            'business_ifsc_code' => ['nullable', 'string', 'max:20'],
            'business_bank_branch' => ['nullable', 'string', 'max:150'],
            'business_upi_id' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:1024', 'dimensions:max_width=2000,max_height=2000'],
            'remove_logo' => ['nullable', 'boolean'],
        ], [
            'business_gstin.regex' => 'Enter a valid 15-character GSTIN (capital letters), or leave blank if not registered.',
            'business_pan.regex' => 'Enter a valid 10-character PAN (capital letters).',
            'business_phone.regex' => 'Enter a valid phone number.',
            'business_alt_phone.regex' => 'Enter a valid phone number.',
        ]);

        $values = [];
        foreach (['name', 'tagline', 'address', 'state', 'phone', 'alt_phone', 'email', 'gstin', 'pan', 'bank_name', 'account_name', 'account_number', 'ifsc_code', 'bank_branch', 'upi_id'] as $f) {
            $values["business.$f"] = $data["business_$f"] ?? null;
        }

        $currentLogo = $this->settings->get('business.logo');
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $name = 'logo-'.Str::random(10).'.'.$file->extension();
            $file->storeAs('branding', $name, 'uploads');
            $values['business.logo'] = 'branding/'.$name;
            $this->deleteLogo($currentLogo);
        } elseif ($request->boolean('remove_logo')) {
            $values['business.logo'] = null;
            $this->deleteLogo($currentLogo);
        }

        return $values;
    }

    private function documents(Request $request): array
    {
        $data = $request->validate([
            'invoice_header' => ['nullable', 'string', 'max:500'],
            'invoice_footer' => ['nullable', 'string', 'max:500'],
            'receipt_footer' => ['nullable', 'string', 'max:500'],
            'show_signature' => ['nullable', 'boolean'],
            'tax_enabled' => ['nullable', 'boolean'],
            'tax_label' => ['required_if:tax_enabled,1', 'nullable', 'string', 'max:20'],
            'tax_default_rate' => ['nullable', 'numeric', 'min:0', 'max:28'],
        ]);

        return [
            'documents.invoice_header' => $data['invoice_header'] ?? null,
            'documents.invoice_footer' => $data['invoice_footer'] ?? null,
            'documents.receipt_footer' => $data['receipt_footer'] ?? null,
            'documents.show_signature' => $request->boolean('show_signature'),
            'tax.enabled' => $request->boolean('tax_enabled'),
            'tax.label' => $data['tax_label'] ?? 'GST',
            'tax.default_rate' => isset($data['tax_default_rate']) ? bc_round((string) $data['tax_default_rate'], 2) : '0.00',
        ];
    }

    private function numbering(Request $request): array
    {
        $rule = ['required', 'string', 'max:10', 'regex:/^[A-Z0-9\-]+$/'];
        $data = $request->validate([
            'purchase' => $rule, 'sales_order' => $rule, 'sales_invoice' => $rule,
            'farmer_payment' => $rule, 'customer_receipt' => $rule, 'expense' => $rule,
            'farmer' => $rule, 'customer' => $rule,
            'padding' => ['required', 'integer', 'between:3,8'],
        ], ['regex' => 'Use capital letters, digits and hyphens only.']);

        return collect($data)->mapWithKeys(fn ($v, $k) => ["numbering.$k" => $v])->all();
    }

    private function system(Request $request): array
    {
        $data = $request->validate([
            'date_format' => ['required', Rule::in(array_keys(config('kisan.date_formats')))],
            'financial_year_start_month' => ['required', 'integer', 'between:1,12'],
            'session_timeout_minutes' => ['required', 'integer', 'between:5,480'],
            'low_stock_threshold_qtl' => ['required', 'numeric', 'min:0', 'max:100000'],
            'allow_negative_stock' => ['nullable', 'boolean'],
            'delayed_bonus_enabled' => ['nullable', 'boolean'],
            'delayed_bonus_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'delayed_bonus_mode' => ['nullable', 'in:monthly,flat,custom'],
        ]);

        $values = [
            'locale.date_format' => $data['date_format'],
            'locale.financial_year_start_month' => $data['financial_year_start_month'],
            'security.session_timeout_minutes' => $data['session_timeout_minutes'],
            'inventory.low_stock_threshold_qtl' => bc_round((string) $data['low_stock_threshold_qtl'], 2),
            'inventory.allow_negative_stock' => $request->boolean('allow_negative_stock'),
        ];

        if ($request->has('delayed_bonus_section_present')) {
            $values['procurement.delayed_bonus_enabled'] = $request->boolean('delayed_bonus_enabled') ? '1' : '0';
            $values['procurement.delayed_bonus_rate'] = bc_round((string) ($data['delayed_bonus_rate'] ?? '1.00'), 2);
            $values['procurement.delayed_bonus_mode'] = $data['delayed_bonus_mode'] ?? 'monthly';
        }

        return $values;
    }

    private function mail(Request $request): array
    {
        $data = $request->validate([
            'mailer' => ['required', 'in:smtp,log'],
            'host' => ['nullable', 'string', 'max:190'],
            'port' => ['nullable', 'integer', 'between:1,65535'],
            'encryption' => ['nullable', 'in:ssl,tls,none'],
            'username' => ['nullable', 'string', 'max:190'],
            'password' => ['nullable', 'string', 'max:190'],
            'from_address' => ['required', 'email', 'max:190'],
            'from_name' => ['required', 'string', 'max:120'],
        ]);

        $values = [
            'mail.mailer' => $data['mailer'],
            'mail.host' => $data['host'] ?? null,
            'mail.port' => $data['port'] ? (string) $data['port'] : '465',
            'mail.encryption' => $data['encryption'] ?? 'ssl',
            'mail.username' => $data['username'] ?? null,
            'mail.from_address' => $data['from_address'],
            'mail.from_name' => $data['from_name'],
        ];

        // Only update password if a new one was entered
        if (filled($request->input('password'))) {
            $values['mail.password'] = (string) $request->input('password');
        }

        return $values;
    }

    public function sendTestEmail(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'test_email' => ['required', 'email', 'max:190'],
        ], [
            'test_email.required' => 'Please provide a recipient email address for the test.',
            'test_email.email' => 'Please provide a valid recipient email address.',
        ]);

        try {
            MailConfigService::apply();

            $s = $this->settings->all();
            $mailer = $s['mail.mailer'] ?? config('mail.default', 'log');
            $details = [
                'mailer' => $mailer,
                'host' => $s['mail.host'] ?? config('mail.mailers.smtp.host', '127.0.0.1'),
                'port' => (int) ($s['mail.port'] ?? config('mail.mailers.smtp.port', 465)),
                'encryption' => $s['mail.encryption'] ?? 'ssl',
                'from_address' => $s['mail.from_address'] ?? config('mail.from.address'),
                'from_name' => $s['mail.from_name'] ?? config('mail.from.name'),
                'sent_at' => now()->format('d M Y, h:i:s A'),
                'sent_by' => $request->user()?->name ?? 'Administrator',
            ];

            Mail::to($data['test_email'])->send(new TestSmtpMail($details));

            $msg = $mailer === 'log'
                ? "Test email written to local log (storage/logs/laravel.log) successfully for {$data['test_email']}."
                : "Test email successfully delivered to {$data['test_email']} via {$details['host']}:{$details['port']}!";

            return redirect()->route('settings.edit', ['tab' => 'mail'])
                ->with('success', $msg);
        } catch (Throwable $e) {
            return redirect()->route('settings.edit', ['tab' => 'mail'])
                ->with('error', 'SMTP Connection Error: ' . $e->getMessage());
        }
    }

    private function whatsapp(Request $request): array
    {
        $data = $request->validate([
            'whatsapp_enabled' => ['nullable', 'boolean'],
            'whatsapp_provider' => ['required', 'in:meta,generic,log'],
            'meta_phone_number_id' => ['nullable', 'string', 'max:190'],
            'meta_access_token' => ['nullable', 'string', 'max:500'],
            'meta_waba_id' => ['nullable', 'string', 'max:190'],
            'meta_api_version' => ['nullable', 'string', 'max:20'],
            'generic_endpoint' => ['nullable', 'url', 'max:255'],
            'generic_api_key' => ['nullable', 'string', 'max:255'],
            'sender_phone' => ['nullable', 'string', 'max:30'],
            'auto_notify_purchase' => ['nullable', 'boolean'],
            'auto_notify_payment' => ['nullable', 'boolean'],
            'auto_notify_sale' => ['nullable', 'boolean'],
        ]);

        $values = [
            'whatsapp.enabled' => $request->boolean('whatsapp_enabled') ? '1' : '0',
            'whatsapp.provider' => $data['whatsapp_provider'],
            'whatsapp.meta_phone_number_id' => $data['meta_phone_number_id'] ?? null,
            'whatsapp.meta_waba_id' => $data['meta_waba_id'] ?? null,
            'whatsapp.meta_api_version' => $data['meta_api_version'] ?: 'v21.0',
            'whatsapp.generic_endpoint' => $data['generic_endpoint'] ?? null,
            'whatsapp.sender_phone' => $data['sender_phone'] ?? null,
            'whatsapp.auto_notify_purchase' => $request->boolean('auto_notify_purchase') ? '1' : '0',
            'whatsapp.auto_notify_payment' => $request->boolean('auto_notify_payment') ? '1' : '0',
            'whatsapp.auto_notify_sale' => $request->boolean('auto_notify_sale') ? '1' : '0',
        ];

        // Keep existing token if submitted blank
        if (filled($request->input('meta_access_token'))) {
            $values['whatsapp.meta_access_token'] = (string) $request->input('meta_access_token');
        }
        if (filled($request->input('generic_api_key'))) {
            $values['whatsapp.generic_api_key'] = (string) $request->input('generic_api_key');
        }

        return $values;
    }

    public function sendTestWhatsApp(Request $request, WhatsAppService $whatsApp): RedirectResponse
    {
        $data = $request->validate([
            'test_mobile' => ['required', 'string', 'min:10', 'max:20'],
            'test_message' => ['nullable', 'string', 'max:500'],
        ], [
            'test_mobile.required' => 'Please provide a recipient mobile number.',
            'test_mobile.min' => 'Please provide a valid 10-digit mobile number.',
        ]);

        $bizName = $this->settings->get('business.name', 'Kisan Enterprises');
        $defaultMsg = "🌾 *{$bizName}* - WhatsApp API Test\n\n"
            . "✅ This is a live verification message confirming your WhatsApp API connectivity.\n"
            . "⏱️ Sent at: " . now()->format('d M Y, h:i A');

        $message = ! empty($data['test_message']) ? $data['test_message'] : $defaultMsg;

        $result = $whatsApp->send($data['test_mobile'], $message);

        if ($result['success']) {
            return redirect()->route('settings.edit', ['tab' => 'whatsapp'])
                ->with('success', $result['message']);
        }

        return redirect()->route('settings.edit', ['tab' => 'whatsapp'])
            ->with('error', $result['message']);
    }

    private function deleteLogo(?string $path): void
    {
        if ($path && str_starts_with($path, 'branding/')) {
            Storage::disk('uploads')->delete($path);
        }
    }
}
