<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    public function __construct(private SettingsService $settings) {}

    /**
     * Normalize Indian or international phone numbers to digits only.
     * e.g. "9876543210" -> "919876543210"
     * "+91 98765-43210" -> "919876543210"
     */
    public function normalizeMobile(?string $mobile): ?string
    {
        if (empty($mobile)) {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', $mobile);

        if (strlen($clean) === 10) {
            return '91' . $clean;
        }

        if (strlen($clean) === 11 && str_starts_with($clean, '0')) {
            return '91' . substr($clean, 1);
        }

        if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
            return $clean;
        }

        return strlen($clean) >= 10 ? $clean : null;
    }

    /**
     * Generate direct wa.me web link for 1-click fallback.
     */
    public function formatClickToChatUrl(string $mobile, string $message): string
    {
        $normalized = $this->normalizeMobile($mobile) ?? preg_replace('/[^0-9]/', '', $mobile);
        return 'https://wa.me/' . $normalized . '?text=' . rawurlencode($message);
    }

    /**
     * Send WhatsApp message via active provider (Meta Cloud API, Generic Webhook, or Log).
     *
     * @return array{success: bool, message: string, provider: string, response?: mixed, message_id?: string}
     */
    public function send(string $mobile, string $message, ?string $templateName = null, array $templateParams = []): array
    {
        $recipient = $this->normalizeMobile($mobile);
        if (! $recipient) {
            return [
                'success' => false,
                'message' => 'Invalid or missing mobile number (minimum 10 digits required).',
                'provider' => 'none',
            ];
        }

        $s = $this->settings->all();
        $enabled = !empty($s['whatsapp.enabled']) && in_array((string)$s['whatsapp.enabled'], ['1', 'true', 'on'], true);
        $provider = $s['whatsapp.provider'] ?? 'log';

        // Safe Log Mode (when disabled, or explicitly set to 'log')
        if (! $enabled || $provider === 'log') {
            Log::info("WhatsApp Message [LOG MODE] to {$recipient}: {$message}");
            return [
                'success' => true,
                'message' => "Message logged successfully for +{$recipient} (Log mode active).",
                'provider' => 'log',
            ];
        }

        try {
            if ($provider === 'meta') {
                return $this->sendViaMeta($recipient, $message, $s, $templateName, $templateParams);
            }

            if ($provider === 'generic') {
                return $this->sendViaGeneric($recipient, $message, $s);
            }

            return [
                'success' => false,
                'message' => "Unsupported WhatsApp provider: {$provider}",
                'provider' => $provider,
            ];
        } catch (Throwable $e) {
            Log::error("WhatsApp dispatch exception: " . $e->getMessage(), ['exception' => $e]);
            return [
                'success' => false,
                'message' => 'WhatsApp Service Exception: ' . $e->getMessage(),
                'provider' => $provider,
            ];
        }
    }

    private function sendViaMeta(string $recipient, string $message, array $s, ?string $templateName, array $templateParams): array
    {
        $phoneId = $s['whatsapp.meta_phone_number_id'] ?? null;
        $token = $s['whatsapp.meta_access_token'] ?? null;
        $version = $s['whatsapp.meta_api_version'] ?? 'v21.0';

        if (empty($phoneId) || empty($token)) {
            return [
                'success' => false,
                'message' => 'Meta WhatsApp credentials missing: Phone Number ID or Access Token is blank.',
                'provider' => 'meta',
            ];
        }

        $url = "https://graph.facebook.com/{$version}/{$phoneId}/messages";

        if ($templateName) {
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $recipient,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => ['code' => 'en'],
                    'components' => $templateParams,
                ],
            ];
        } else {
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $recipient,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $message,
                ],
            ];
        }

        $res = Http::withToken($token)
            ->timeout(12)
            ->acceptJson()
            ->post($url, $payload);

        if ($res->successful()) {
            $body = $res->json();
            $msgId = $body['messages'][0]['id'] ?? null;
            return [
                'success' => true,
                'message' => "WhatsApp message delivered to +{$recipient} via Meta Cloud API.",
                'provider' => 'meta',
                'message_id' => $msgId,
                'response' => $body,
            ];
        }

        $errorMsg = $res->json('error.message') ?? $res->body();
        return [
            'success' => false,
            'message' => "Meta WhatsApp API Error ({$res->status()}): {$errorMsg}",
            'provider' => 'meta',
            'response' => $res->json(),
        ];
    }

    private function sendViaGeneric(string $recipient, string $message, array $s): array
    {
        $endpoint = $s['whatsapp.generic_endpoint'] ?? null;
        $apiKey = $s['whatsapp.generic_api_key'] ?? null;
        $sender = $s['whatsapp.sender_phone'] ?? null;

        if (empty($endpoint)) {
            return [
                'success' => false,
                'message' => 'Generic WhatsApp gateway endpoint URL is missing.',
                'provider' => 'generic',
            ];
        }

        $request = Http::timeout(12)->acceptJson();
        if (! empty($apiKey)) {
            $request = $request->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'X-API-KEY' => $apiKey,
            ]);
        }

        $payload = [
            'to' => $recipient,
            'phone' => $recipient,
            'mobile' => $recipient,
            'message' => $message,
            'text' => $message,
            'sender' => $sender,
        ];

        $res = $request->post($endpoint, $payload);

        if ($res->successful()) {
            return [
                'success' => true,
                'message' => "WhatsApp message delivered to +{$recipient} via Generic Gateway.",
                'provider' => 'generic',
                'response' => $res->json() ?? $res->body(),
            ];
        }

        return [
            'success' => false,
            'message' => "Generic Gateway Error ({$res->status()}): " . $res->body(),
            'provider' => 'generic',
        ];
    }

    /**
     * Build formatted message for Purchase Receipt.
     */
    public function formatPurchaseMessage(Purchase $purchase): string
    {
        $bizName = $this->settings->get('business.name', 'Kisan Enterprises');
        $farmerName = $purchase->farmer?->name ?? 'किसान भाई';
        $cropName = $purchase->crop?->name ?? 'फसल';
        $weight = number_format((float) $purchase->net_weight_qtl, 2);
        $amount = number_format((float) $purchase->net_payable_amount, 2);
        $receiptNo = $purchase->purchase_number;

        return "🌾 *{$bizName}*\n\n"
            . "नमस्ते *{$farmerName}* जी,\n"
            . "आपकी फसल खरीद रसीद दर्ज कर ली गई है:\n\n"
            . "📄 *रसीद नं:* {$receiptNo}\n"
            . "🌱 *फसल:* {$cropName}\n"
            . "⚖️ *शुद्ध तौल:* {$weight} क्विंटल\n"
            . "💰 *कुल देय राशि:* ₹{$amount}\n\n"
            . "धन्यवाद!\n"
            . "कॉल/सहायता: " . ($this->settings->get('business.phone') ?? 'मंडी कार्यालय');
    }

    /**
     * Build formatted message for Farmer Payment Voucher.
     */
    public function formatPaymentMessage(PurchasePayment $payment): string
    {
        $bizName = $this->settings->get('business.name', 'Kisan Enterprises');
        $farmerName = $payment->farmer?->name ?? 'किसान भाई';
        $amount = number_format((float) $payment->effective_payout, 2);
        $methodLabel = PurchasePayment::METHODS[$payment->payment_method] ?? strtoupper((string) $payment->payment_method);
        $voucherNo = $payment->voucher_number;
        $ref = $payment->transaction_reference ? " (Ref: {$payment->transaction_reference})" : '';

        return "🌾 *{$bizName}*\n\n"
            . "नमस्ते *{$farmerName}* जी,\n"
            . "आपको भुगतान सफलतापूर्वक प्रेषित कर दिया गया है:\n\n"
            . "📄 *वाउचर नं:* {$voucherNo}\n"
            . "💵 *भुगतान राशि:* ₹{$amount}\n"
            . "🏦 *माध्यम:* {$methodLabel}{$ref}\n\n"
            . "धन्यवाद!";
    }

    /**
     * Build formatted message for Sales Invoice.
     */
    public function formatSalesInvoiceMessage(SalesInvoice $invoice): string
    {
        $bizName = $this->settings->get('business.name', 'Kisan Enterprises');
        $customerName = $invoice->customer?->name ?? 'Valued Customer';
        $invoiceNo = $invoice->invoice_number;
        $amount = number_format((float) $invoice->total_amount, 2);
        $vehicle = $invoice->vehicle_number ? "Vehicle: {$invoice->vehicle_number}" : '';

        return "🌾 *{$bizName}*\n\n"
            . "Dear *{$customerName}*,\n"
            . "Your sales invoice has been finalized:\n\n"
            . "📄 *Invoice No:* {$invoiceNo}\n"
            . "💰 *Total Amount:* ₹{$amount}\n"
            . ($vehicle ? "🚚 *{$vehicle}*\n\n" : "\n")
            . "Thank you for doing business with us!";
    }
}

