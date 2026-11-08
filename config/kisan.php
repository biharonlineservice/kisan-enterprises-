<?php

/*
|--------------------------------------------------------------------------
| Kisan Enterprises ERP configuration
|--------------------------------------------------------------------------
| - permissions: the complete permission catalogue (synced to DB by RolePermissionSeeder)
| - roles: default permission set for each system role (Admin implicitly has all)
| - settings: default values for business_settings (used until Admin saves a value)
*/

return [

    'permissions' => [
        'General' => [
            'dashboard.view' => 'View dashboard',
        ],
        'Administration' => [
            'users.manage' => 'Create and manage user accounts',
            'roles.manage' => 'Edit role permissions',
            'settings.manage' => 'Change business settings',
            'audit.view' => 'View audit logs',
            'backup.view' => 'View backup & maintenance information',
            'website.manage' => 'Manage public website content',
        ],
        'Farmers' => [
            'farmers.view' => 'View farmers',
            'farmers.create' => 'Register farmers',
            'farmers.edit' => 'Edit farmers',
            'farmers.bank' => 'View/edit farmer bank & ID details',
            'farmers.export' => 'Export farmer records',
        ],
        'Masters' => [
            'masters.manage' => 'Manage crops, varieties and warehouses',
        ],
        'Customers' => [
            'customers.view' => 'View customers & mills',
            'customers.manage' => 'Create/edit customers & mills',
        ],
        'Purchases' => [
            'purchases.view' => 'View purchases',
            'purchases.create' => 'Create/edit draft purchases',
            'purchases.finalize' => 'Finalize purchases',
            'purchases.reverse' => 'Cancel/reverse finalized purchases',
        ],
        'Inventory' => [
            'inventory.view' => 'View stock & movements',
            'inventory.transfer' => 'Transfer stock between warehouses',
            'inventory.adjust' => 'Request stock adjustments',
            'inventory.approve' => 'Approve stock adjustments',
        ],
        'Sales' => [
            'sales.view' => 'View sales orders & invoices',
            'sales.create' => 'Create sales orders & draft invoices',
            'sales.finalize' => 'Finalize sales invoices',
            'sales.reverse' => 'Cancel/return sales invoices',
            'dispatch.manage' => 'Record dispatch & delivery',
        ],
        'Payments' => [
            'farmer_payments.view' => 'View farmer payments',
            'farmer_payments.create' => 'Record farmer payments',
            'customer_receipts.view' => 'View customer receipts',
            'customer_receipts.create' => 'Record customer receipts',
            'payments.reverse' => 'Reverse payments/receipts',
        ],
        'Accounts' => [
            'expenses.view' => 'View expenses',
            'expenses.create' => 'Record expenses',
            'expenses.approve' => 'Approve expenses',
            'accounts.view' => 'View cashbook, bankbook & balances',
            'accounts.manage' => 'Manage cash/bank accounts & transfers',
        ],
        'Reports' => [
            'reports.purchase' => 'Purchase reports',
            'reports.sales' => 'Sales reports',
            'reports.inventory' => 'Stock reports',
            'reports.finance' => 'Financial reports (outstanding, cashbook, P&L)',
            'reports.activity' => 'Employee activity, cancellations & audit reports',
        ],
    ],

    'roles' => [
        'admin' => [
            'display_name' => 'Admin',
            'description' => 'Full access to all modules, users and settings.',
            'permissions' => ['*'],
        ],
        'purchase_sales' => [
            'display_name' => 'Purchase & Sales Manager',
            'description' => 'Farmers, purchases, inventory availability, sales and dispatch.',
            'permissions' => [
                'dashboard.view',
                'farmers.view', 'farmers.create', 'farmers.edit', 'farmers.export',
                'customers.view', 'customers.manage',
                'purchases.view', 'purchases.create', 'purchases.finalize',
                'inventory.view', 'inventory.transfer', 'inventory.adjust',
                'sales.view', 'sales.create', 'sales.finalize', 'dispatch.manage',
                'farmer_payments.view', 'customer_receipts.view',
                'reports.purchase', 'reports.sales', 'reports.inventory',
            ],
        ],
        'accounts' => [
            'display_name' => 'Accounts & Operations Manager',
            'description' => 'Payments, receipts, expenses, cash/bank books and financial reports.',
            'permissions' => [
                'dashboard.view',
                'farmers.view', 'farmers.bank',
                'customers.view',
                'purchases.view', 'sales.view', 'inventory.view',
                'farmer_payments.view', 'farmer_payments.create',
                'customer_receipts.view', 'customer_receipts.create',
                'expenses.view', 'expenses.create',
                'accounts.view', 'accounts.manage',
                'reports.purchase', 'reports.sales', 'reports.finance',
            ],
        ],
    ],

    /*
    | Setting defaults. Business contact details are intentionally blank:
    | the owner must enter verified details in Settings.
    */
    'settings' => [
        // Business profile (Legal Entity & ERP Documents)
        'business.name' => 'Kisan Enterprises',
        'business.tagline' => 'Agricultural Crop Trading',
        'business.logo' => 'branding/kisanhub-logo.png',
        'business.address' => null,
        'business.phone' => null,
        'business.alt_phone' => null,
        'business.email' => null,
        'business.gstin' => null,
        'business.pan' => null,
        'business.state' => null,

        // Public Website Branding
        'website.brand_name' => 'KisanHub',

        // Bank remittance details
        'business.bank_name' => 'State Bank of India',
        'business.account_name' => 'Kisan Enterprises',
        'business.account_number' => '32109876543',
        'business.ifsc_code' => 'SBIN0001234',
        'business.bank_branch' => 'Krishi Upaj Mandi Samiti, Chandauli',
        'business.upi_id' => 'kisanenterprises@sbi',

        // Documents
        'documents.invoice_header' => null,
        'documents.invoice_footer' => 'This is a computer generated document.',
        'documents.receipt_footer' => 'Please verify weight and amount before signing.',
        'documents.show_signature' => '1',

        // Tax (off by default — do not assume GST applies)
        'tax.enabled' => '0',
        'tax.label' => 'GST',
        'tax.default_rate' => '0',

        // Numbering prefixes
        'numbering.purchase' => 'PUR',
        'numbering.sales_order' => 'SO',
        'numbering.sales_invoice' => 'INV',
        'numbering.farmer_payment' => 'PV',
        'numbering.customer_receipt' => 'RCPT',
        'numbering.expense' => 'EXP',
        'numbering.farmer' => 'FRM',
        'numbering.customer' => 'CUS',
        'numbering.padding' => '5',

        // Localisation
        'locale.currency_symbol' => '₹',
        'locale.date_format' => 'd-m-Y',
        'locale.financial_year_start_month' => '4',

        // Security & operations
        'security.session_timeout_minutes' => '30',
        'inventory.allow_negative_stock' => '0',

        // Procurement delayed payment bonus
        'procurement.delayed_bonus_enabled' => '1',
        'procurement.delayed_bonus_rate' => '1.00',
        'procurement.delayed_bonus_mode' => 'monthly',
        'inventory.low_stock_threshold_qtl' => '10',

        // Backup information (displayed to Admin)
        'maintenance.last_backup_at' => null,
        'maintenance.backup_notes' => null,

        // Email & SMTP Mailer configuration
        'mail.mailer' => 'log',
        'mail.host' => 'smtp.hostinger.com',
        'mail.port' => '465',
        'mail.encryption' => 'ssl',
        'mail.username' => null,
        'mail.password' => null,
        'mail.from_address' => 'noreply@kisanenterprises.in',
        'mail.from_name' => 'Kisan Enterprises',

        // WhatsApp API configuration
        'whatsapp.enabled' => '0',
        'whatsapp.provider' => 'log', // 'meta', 'generic', 'log'
        'whatsapp.meta_phone_number_id' => null,
        'whatsapp.meta_access_token' => null,
        'whatsapp.meta_waba_id' => null,
        'whatsapp.meta_api_version' => 'v21.0',
        'whatsapp.generic_endpoint' => null,
        'whatsapp.generic_api_key' => null,
        'whatsapp.sender_phone' => null,
        'whatsapp.auto_notify_purchase' => '1',
        'whatsapp.auto_notify_payment' => '1',
        'whatsapp.auto_notify_sale' => '0',
    ],

    // Allowed date formats offered in Settings
    'date_formats' => [
        'd-m-Y' => '28-10-2026',
        'd/m/Y' => '28/10/2026',
        'd M Y' => '28 Oct 2026',
        'Y-m-d' => '2026-10-28',
    ],
];
