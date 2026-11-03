<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\Customer;
use App\Models\Farmer;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DocumentNumber;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Crops & Varieties
            $crops = [
                [
                    'name' => 'Paddy (Dhan)',
                    'code' => 'PADDY',
                    'unit' => 'quintal',
                    'hsn_code' => '1006',
                    'current_purchase_rate' => 2300.00,
                    'max_purchase_rate' => 2450.00,
                    'rate_note' => 'FAQ / Grade A Clean',
                    'description' => 'Common and fine paddy grain procured directly from farmers.',
                    'varieties' => [
                        ['name' => 'Sona Masoori', 'code' => 'SM-01', 'moisture' => 14.0, 'grade' => 'Grade A'],
                        ['name' => 'Katarni', 'code' => 'KAT-01', 'moisture' => 13.5, 'grade' => 'Grade A (Aromatic)'],
                        ['name' => 'Basmati 1121', 'code' => 'BAS-1121', 'moisture' => 12.5, 'grade' => 'Premium Aromatic'],
                        ['name' => 'Swarna (Mansuri)', 'code' => 'SW-01', 'moisture' => 14.5, 'grade' => 'Grade B (Standard)'],
                    ],
                ],
                [
                    'name' => 'Wheat (Gehun)',
                    'code' => 'WHEAT',
                    'unit' => 'quintal',
                    'hsn_code' => '1001',
                    'current_purchase_rate' => 2425.00,
                    'max_purchase_rate' => 2600.00,
                    'rate_note' => 'Milling & Sharbati',
                    'description' => 'High quality milling and baking wheat.',
                    'varieties' => [
                        ['name' => 'Sharbati', 'code' => 'SHAR-01', 'moisture' => 12.0, 'grade' => 'Grade A'],
                        ['name' => 'Lokwan', 'code' => 'LOK-01', 'moisture' => 12.0, 'grade' => 'Grade A'],
                        ['name' => 'HD-2967', 'code' => 'HD-2967', 'moisture' => 12.5, 'grade' => 'Grade B'],
                    ],
                ],
                [
                    'name' => 'Maize (Makka)',
                    'code' => 'MAIZE',
                    'unit' => 'quintal',
                    'hsn_code' => '1005',
                    'current_purchase_rate' => 2150.00,
                    'max_purchase_rate' => 2250.00,
                    'rate_note' => 'Moisture <= 14%',
                    'description' => 'Yellow corn/maize for feed and starch manufacturing.',
                    'varieties' => [
                        ['name' => 'Yellow Hybrid', 'code' => 'Y-HYB', 'moisture' => 14.0, 'grade' => 'Grade A'],
                        ['name' => 'White Flint', 'code' => 'W-FLINT', 'moisture' => 14.0, 'grade' => 'Grade B'],
                    ],
                ],
                [
                    'name' => 'Mustard (Sarson)',
                    'code' => 'MUSTARD',
                    'unit' => 'quintal',
                    'hsn_code' => '1207',
                    'current_purchase_rate' => 5650.00,
                    'max_purchase_rate' => 5850.00,
                    'rate_note' => 'High Oil Grade',
                    'description' => 'Oilseed mustard for expellers and processing units.',
                    'varieties' => [
                        ['name' => 'Pusa Bold', 'code' => 'PUSA-B', 'moisture' => 8.0, 'grade' => 'Grade A (High Oil)'],
                        ['name' => 'Varuna', 'code' => 'VARUNA', 'moisture' => 8.5, 'grade' => 'Grade A'],
                    ],
                ],
            ];

            foreach ($crops as $cData) {
                $existingCrop = Crop::where('code', $cData['code'])->first();
                $crop = Crop::updateOrCreate(['code' => $cData['code']], [
                    'name' => $cData['name'],
                    'unit' => $cData['unit'],
                    'hsn_code' => $cData['hsn_code'],
                    'current_purchase_rate' => $existingCrop?->current_purchase_rate ?? $cData['current_purchase_rate'],
                    'max_purchase_rate' => $existingCrop?->max_purchase_rate ?? $cData['max_purchase_rate'],
                    'rate_note' => $existingCrop?->rate_note ?? $cData['rate_note'],
                    'show_rate_on_website' => $existingCrop ? $existingCrop->show_rate_on_website : true,
                    'rate_updated_at' => $existingCrop?->rate_updated_at ?? now(),
                    'description' => $cData['description'],
                    'is_active' => true,
                ]);

                foreach ($cData['varieties'] as $vData) {
                    $crop->varieties()->updateOrCreate(['name' => $vData['name']], [
                        'code' => $vData['code'],
                        'standard_moisture_pct' => $vData['moisture'],
                        'default_grade' => $vData['grade'],
                        'is_active' => true,
                    ]);
                }
            }

            // 2. Warehouses
            $supervisor = User::where('email', 'purchase@kisan.local')->first() ?? User::first();
            $warehouses = [
                ['name' => 'Main Mandi Godown No. 1', 'code' => 'WH-01', 'capacity' => 10000.00, 'phone' => '9000000001', 'address' => 'Plot 12, Krishi Upaj Mandi Premise'],
                ['name' => 'Processing Yard Godown No. 2', 'code' => 'WH-02', 'capacity' => 6000.00, 'phone' => '9000000002', 'address' => 'Industrial Area Road, Near Weighbridge'],
                ['name' => 'Railway Siding Storage', 'code' => 'WH-03', 'capacity' => 8000.00, 'phone' => '9000000003', 'address' => 'Goods Shed Road, Platform 3'],
                ['name' => 'Direct Sale / Mandi Transit', 'code' => 'WH-DIRECT', 'capacity' => 100000.00, 'phone' => null, 'address' => 'Direct Mandi Vehicle-to-Vehicle Dispatch'],
            ];

            foreach ($warehouses as $wh) {
                Warehouse::updateOrCreate(['code' => $wh['code']], [
                    'name' => $wh['name'],
                    'capacity_quintals' => $wh['capacity'],
                    'supervisor_id' => $supervisor?->id,
                    'phone' => $wh['phone'],
                    'address' => $wh['address'],
                    'is_active' => true,
                ]);
            }

            // 3. Sample Farmers (only in local / development)
            if (! app()->environment('production')) {
                $sampleFarmers = [
                    [
                        'name' => 'Ramashankar Singh',
                        'mobile' => '9835012345',
                        'village' => 'Rampur',
                        'panchayat' => 'Rampur Paschim',
                        'block' => 'Bikramganj',
                        'district' => 'Rohtas',
                        'state' => 'Bihar',
                        'bank_name' => 'State Bank of India',
                        'bank_branch' => 'Bikramganj',
                        'bank_account_number' => '30987654321',
                        'bank_ifsc' => 'SBIN0001234',
                        'id_type' => 'Aadhaar',
                        'id_number' => '4567 8901 2345',
                        'opening_balance' => 0.00,
                    ],
                    [
                        'name' => 'Dharmendra Yadav',
                        'mobile' => '9431023456',
                        'village' => 'Karasath',
                        'panchayat' => 'Karasath',
                        'block' => 'Udwantnagar',
                        'district' => 'Bhojpur',
                        'state' => 'Bihar',
                        'bank_name' => 'Punjab National Bank',
                        'bank_branch' => 'Ara',
                        'bank_account_number' => '1234000100234567',
                        'bank_ifsc' => 'PUNB0123400',
                        'id_type' => 'KCC',
                        'id_number' => 'KCC-987654',
                        'opening_balance' => 5000.00,
                    ],
                    [
                        'name' => 'Kapildev Tiwari',
                        'mobile' => '9122034567',
                        'village' => 'Buxar Diyara',
                        'panchayat' => 'Ahirauli',
                        'block' => 'Buxar',
                        'district' => 'Buxar',
                        'state' => 'Bihar',
                        'bank_name' => 'Central Bank of India',
                        'bank_branch' => 'Buxar Bazar',
                        'bank_account_number' => '2233445566',
                        'bank_ifsc' => 'CBIN0281234',
                        'id_type' => 'Voter ID',
                        'id_number' => 'BR/02/034/123456',
                        'opening_balance' => 0.00,
                    ],
                ];

                foreach ($sampleFarmers as $fData) {
                    if (! Farmer::where('mobile', $fData['mobile'])->exists()) {
                        Farmer::create([
                            ...$fData,
                            'farmer_id' => DocumentNumber::next('farmer'),
                            'registration_date' => now()->subDays(rand(5, 60)),
                            'is_active' => true,
                            'created_by' => $supervisor?->id,
                        ]);
                    }
                }

                // 4. Sample Customers / Rice Mills
                $sampleCustomers = [
                    [
                        'name' => 'Mahavir Rice Mill Pvt Ltd',
                        'customer_type' => 'rice_mill',
                        'contact_person' => 'Rajesh Kumar Agarwal',
                        'mobile' => '9835100001',
                        'email' => 'mahavir.ricemill@example.com',
                        'city' => 'Bikramganj',
                        'state' => 'Bihar',
                        'address' => 'Industrial Estate, Sasaram Road',
                        'gstin' => '10AABCM1234F1Z5',
                        'pan' => 'AABCM1234F',
                        'credit_limit' => 5000000.00,
                        'payment_terms_days' => 15,
                        'opening_balance' => 0.00,
                    ],
                    [
                        'name' => 'Maa Sharda Grain Traders',
                        'customer_type' => 'wholesaler',
                        'contact_person' => 'Sanjay Gupta',
                        'mobile' => '9431100002',
                        'city' => 'Patna',
                        'state' => 'Bihar',
                        'address' => 'Shop No 45, Gulzarbagh Mandi',
                        'gstin' => '10ABCDE5678G1Z2',
                        'pan' => 'ABCDE5678G',
                        'credit_limit' => 2000000.00,
                        'payment_terms_days' => 7,
                        'opening_balance' => 25000.00,
                    ],
                    [
                        'name' => 'Bhojpur Agro Flour Mill',
                        'customer_type' => 'rice_mill',
                        'contact_person' => 'Amitabh Choudhary',
                        'mobile' => '9122100003',
                        'city' => 'Ara',
                        'state' => 'Bihar',
                        'address' => 'NH-30, Zero Mile, Ara',
                        'gstin' => null, // Unregistered customer support
                        'pan' => 'BBCCC9876D',
                        'credit_limit' => 1500000.00,
                        'payment_terms_days' => 10,
                        'opening_balance' => 0.00,
                    ],
                ];

                foreach ($sampleCustomers as $cData) {
                    if (! Customer::where('name', $cData['name'])->exists()) {
                        Customer::create([
                            ...$cData,
                            'customer_id' => DocumentNumber::next('customer'),
                            'is_active' => true,
                            'created_by' => $supervisor?->id,
                        ]);
                    }
                }
            }
        });
    }
}
