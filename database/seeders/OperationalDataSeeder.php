<?php

namespace Database\Seeders;

use App\Models\CashAccount;
use App\Models\Crop;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Farmer;
use App\Models\Purchase;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WebsiteInquiry;
use App\Services\FinanceService;
use App\Services\ProcurementService;
use App\Services\SalesService;
use App\Services\SettingsService;
use Illuminate\Database\Seeder;

class OperationalDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        if (! $admin) {
            return;
        }

        $procurement = app(ProcurementService::class);
        $sales = app(SalesService::class);
        $finance = app(FinanceService::class);

        $farmers = Farmer::all();
        $customers = Customer::all();
        $warehouses = Warehouse::where('code', '!=', 'WH-DIRECT')->get();
        $crops = Crop::with('varieties')->get();
        $accounts = CashAccount::all();
        $categories = ExpenseCategory::all();

        if ($farmers->isEmpty() || $crops->isEmpty() || $warehouses->isEmpty()) {
            return;
        }

        $paddy = $crops->firstWhere('code', 'PADDY');
        $wheat = $crops->firstWhere('code', 'WHEAT');

        $whMain = $warehouses->first();
        $whDepot = $warehouses->count() > 1 ? $warehouses[1] : $whMain;

        $cashAcc = $accounts->firstWhere('account_type', 'cash') ?? $accounts->first();
        $bankAcc = $accounts->firstWhere('account_type', 'bank') ?? $accounts->first();

        // 1. Initial Cash/Bank balances
        if ($cashAcc) {
            $cashAcc->update(['current_balance' => '250000.00']);
        }
        if ($bankAcc) {
            $bankAcc->update(['current_balance' => '1500000.00']);
        }

        // 2. Purchases (Last 30 days up to today)
        $purchaseDates = [
            now()->subDays(25)->toDateString(),
            now()->subDays(20)->toDateString(),
            now()->subDays(15)->toDateString(),
            now()->subDays(10)->toDateString(),
            now()->subDays(5)->toDateString(),
            now()->subDays(2)->toDateString(),
            now()->toDateString(),
        ];

        foreach ($purchaseDates as $idx => $pDate) {
            $farmer = $farmers[$idx % $farmers->count()];
            $crop = ($idx % 2 === 0) ? $paddy : $wheat;
            $variety = $crop?->varieties->first();
            $wh = ($idx % 3 === 0) ? $whDepot : $whMain;

            $grossKg = (string) (4000 + ($idx * 500));
            $tareKg = '1200';
            $rate = ($crop?->code === 'PADDY') ? '2250.00' : '2300.00';

            $purchase = $procurement->create([
                'purchase_date' => $pDate,
                'farmer_id' => $farmer->id,
                'crop_id' => $crop->id,
                'crop_variety_id' => $variety?->id,
                'warehouse_id' => $wh->id,
                'gross_weight_kg' => $grossKg,
                'tare_weight_kg' => $tareKg,
                'moisture_pct' => '13.5',
                'quality_grade' => 'Grade A',
                'rate_per_qtl' => $rate,
                'deductions_amount' => '0.00',
                'additions_amount' => '0.00',
                'vehicle_number' => 'UP-65-BT-'.(1000 + $idx),
                'driver_name' => 'Driver '.($idx + 1),
                'remarks' => 'Direct mandi procurement from verified farmer',
            ], $admin->id);

            // Finalize all except the last one (keep last one as draft weighment)
            if ($idx < count($purchaseDates) - 1) {
                $procurement->finalize($purchase, $admin->id);

                // Make payment on finalized purchases
                if ($idx % 2 === 0) {
                    $procurement->recordPayment(
                        $purchase,
                        (string) $purchase->fresh()->net_payable_amount,
                        'bank_transfer',
                        'NEFT'.now()->format('Ymd').$idx,
                        'Full RTGS/NEFT settlement to registered farmer bank account',
                        $admin->id
                    );
                } else {
                    $half = bcdiv((string) $purchase->fresh()->net_payable_amount, '2', 2);
                    $procurement->recordPayment(
                        $purchase,
                        $half,
                        'cash',
                        null,
                        'Partial cash advance payment',
                        $admin->id
                    );
                }
            }
        }

        // 3. Sales Invoices
        $salesDates = [
            now()->subDays(18)->toDateString(),
            now()->subDays(12)->toDateString(),
            now()->subDays(4)->toDateString(),
            now()->toDateString(),
        ];

        foreach ($salesDates as $idx => $sDate) {
            $customer = $customers[$idx % $customers->count()];
            $crop = ($idx % 2 === 0) ? $paddy : $wheat;
            $variety = $crop?->varieties->first();
            $wh = $whMain;

            $invoice = $sales->createInvoice([
                'invoice_date' => $sDate,
                'customer_id' => $customer->id,
                'remarks' => 'Commercial grain supply contract dispatch',
                'vehicle_number' => 'UP-65-TR-90'.$idx,
                'driver_name' => 'Truck Driver '.($idx + 1),
                'delivery_destination' => 'Industrial Area, Kanpur',
            ], [
                [
                    'crop_id' => $crop->id,
                    'crop_variety_id' => $variety?->id,
                    'warehouse_id' => $wh->id,
                    'quantity_qtl' => '15.000',
                    'rate_per_qtl' => ($crop?->code === 'PADDY') ? '2450.00' : '2550.00',
                    'tax_rate' => '0.00',
                ],
            ], $admin->id);

            // Finalize all except the last one (keep last one as draft)
            if ($idx < count($salesDates) - 1) {
                $sales->finalizeInvoice($invoice, $admin->id);

                // Receive money for finalized invoice
                $inv = $invoice->fresh();
                if ($idx === 0) {
                    $sales->recordReceipt(
                        $inv,
                        (string) $inv->total_amount,
                        'bank_transfer',
                        'RTGS'.now()->format('Ymd').$idx,
                        'State Bank of India',
                        'Full RTGS invoice settlement from buyer mill',
                        $admin->id
                    );
                } else {
                    $partial = bcdiv((string) $inv->total_amount, '2', 2);
                    $sales->recordReceipt(
                        $inv,
                        $partial,
                        'cheque',
                        'CHQ-550'.$idx,
                        'Punjab National Bank',
                        '50% advance delivery payment',
                        $admin->id
                    );
                }
            }
        }

        // 4. Operational Expenses
        if ($categories->isNotEmpty() && $cashAcc && $bankAcc) {
            $expenseEntries = [
                ['Loading & Unloading Hamali Charges', $categories->firstWhere('name', 'Labour / Hamali')?->id ?? $categories->first()->id, '3500.00', $cashAcc->id, now()->subDays(15)->toDateString()],
                ['Truck Transport & Mandi Freight', $categories->firstWhere('name', 'Transportation / Freight')?->id ?? $categories->first()->id, '12000.00', $bankAcc->id, now()->subDays(10)->toDateString()],
                ['Weighbridge Annual Calibration & Service', $categories->firstWhere('name', 'Weighbridge Maintenance')?->id ?? $categories->first()->id, '4500.00', $bankAcc->id, now()->subDays(6)->toDateString()],
                ['Gunny Bags Purchase (500 Bags)', $categories->firstWhere('name', 'Packaging / Gunny Bags')?->id ?? $categories->first()->id, '18500.00', $bankAcc->id, now()->subDays(3)->toDateString()],
                ['Electricity & Godown Lighting Bill', $categories->firstWhere('name', 'Electricity & Utilities')?->id ?? $categories->first()->id, '6200.00', $bankAcc->id, now()->toDateString()],
            ];

            foreach ($expenseEntries as [$desc, $catId, $amt, $accId, $expDate]) {
                $finance->recordExpense([
                    'expense_date' => $expDate,
                    'expense_category_id' => $catId,
                    'cash_account_id' => $accId,
                    'amount' => $amt,
                    'description' => $desc,
                    'paid_to' => 'Local Service Vendor',
                    'payment_method' => $accId === $cashAcc->id ? 'cash' : 'bank_transfer',
                ], $admin->id);
            }
        }

        // 5. Website Inquiries
        WebsiteInquiry::updateOrCreate(['phone' => '9876543210'], [
            'name' => 'M/s Kisan Agro Processing Mills',
            'email' => 'procurement@kisanagro.com',
            'inquiry_type' => 'bulk_buyer',
            'subject' => 'Requirement of 1,000 MT Wheat (Sharbati)',
            'message' => 'We require 1,000 MT of machine-cleaned Sharbati wheat for our flour mill in Kanpur. Please quote Ex-Godown rates.',
            'status' => WebsiteInquiry::STATUS_NEW,
        ]);

        WebsiteInquiry::updateOrCreate(['phone' => '9811223344'], [
            'name' => 'Shri Devendra Kumar',
            'email' => null,
            'inquiry_type' => 'farmer',
            'subject' => 'Paddy 1509 Harvest Arrival',
            'message' => 'I have 150 Quintals of Paddy 1509 ready in Sakaldiha tehsil. When can I bring my trolley to your Chandauli weighbridge?',
            'status' => WebsiteInquiry::STATUS_RESPONDED,
        ]);

        // 6. Complete Realistic Settings for Business, SMTP & WhatsApp Testing
        $settings = app(SettingsService::class);
        $settings->setMany([
            'business.name' => 'Kisan Enterprises',
            'business.tagline' => 'Agricultural Crop Trading & Modern Warehousing',
            'business.address' => 'Plot No. 42-45, Krishi Upaj Mandi Samiti, GT Road, Chandauli, UP - 232104',
            'business.state' => 'Uttar Pradesh',
            'business.phone' => '9876543210',
            'business.alt_phone' => '05412255678',
            'business.email' => 'info@kisanenterprises.in',
            'business.gstin' => '09AAACK1234M1Z5',
            'business.pan' => 'AAACK1234M',

            // Invoicing & Receipts
            'documents.invoice_header' => 'M/s Kisan Enterprises &bull; Wholesale Agricultural Grain Merchant &bull; Chandauli Mandi',
            'documents.invoice_footer' => 'All disputes subject to Chandauli jurisdiction only. Goods once sold will not be taken back.',
            'documents.receipt_footer' => 'Please verify gross/tare weight and grade before leaving weighbridge premises. Settlement via NEFT/UPI.',
            'documents.show_signature' => '1',
            'tax.enabled' => '0',

            // Email & SMTP (Pre-populated for instant testing)
            'mail.mailer' => 'log',
            'mail.host' => 'smtp.hostinger.com',
            'mail.port' => '465',
            'mail.encryption' => 'ssl',
            'mail.username' => 'info@kisanenterprises.in',
            'mail.password' => 'DemoHostinger#2026',
            'mail.from_address' => 'info@kisanenterprises.in',
            'mail.from_name' => 'Kisan Enterprises',

            // WhatsApp API (Pre-populated for instant testing)
            'whatsapp.enabled' => '1',
            'whatsapp.provider' => 'log',
            'whatsapp.meta_phone_number_id' => '104598214589123',
            'whatsapp.meta_waba_id' => '109823471829341',
            'whatsapp.meta_access_token' => 'EAAGsample_demo_access_token_for_kisan_erp',
            'whatsapp.meta_api_version' => 'v21.0',
            'whatsapp.generic_endpoint' => 'https://api.gateway.local/v1/send',
            'whatsapp.generic_api_key' => 'demo_generic_api_key_8899',
            'whatsapp.sender_phone' => '919876543210',
            'whatsapp.auto_notify_purchase' => '1',
            'whatsapp.auto_notify_payment' => '1',
            'whatsapp.auto_notify_sale' => '1',

            // Delayed payment bonus
            'procurement.delayed_bonus_enabled' => '1',
            'procurement.delayed_bonus_rate' => '1.00',
            'procurement.delayed_bonus_mode' => 'monthly',
        ], $admin->id);
    }
}
