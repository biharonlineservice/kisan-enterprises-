<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Sidebar navigation. An item is shown only when its route exists AND the user
 * holds the permission, so modules appear automatically as they are delivered
 * and users never see links they cannot open.
 */
class Menu
{
    /**
     * Each item: [label, icon, route, permission, active-pattern]
     */
    public const SECTIONS = [
        'Main' => [
            ['Dashboard', 'speedometer2', 'dashboard', 'dashboard.view', 'dashboard'],
        ],
        'Procurement' => [
            ['Farmers', 'people', 'farmers.index', 'farmers.view', 'farmers.*'],
            ['Purchases', 'basket', 'purchases.index', 'purchases.view', 'purchases.*'],
            ['Farmer Payments', 'cash-coin', 'farmer-payments.index', 'farmer_payments.view', 'farmer-payments.*'],
        ],
        'Inventory' => [
            ['Stock', 'box-seam', 'inventory.index', 'inventory.view', 'inventory.index'],
            ['Stock Movements', 'arrow-left-right', 'inventory.movements', 'inventory.view', 'inventory.movements'],
            ['Transfers', 'truck-flatbed', 'stock-transfers.index', 'inventory.view', 'stock-transfers.*'],
            ['Adjustments', 'sliders', 'stock-adjustments.index', 'inventory.view', 'stock-adjustments.*'],
        ],
        'Sales' => [
            ['Customers & Mills', 'building', 'customers.index', 'customers.view', 'customers.*'],
            ['Sales Orders', 'clipboard-check', 'sales-orders.index', 'sales.view', 'sales-orders.*'],
            ['Sales Invoices', 'receipt', 'sales-invoices.index', 'sales.view', 'sales-invoices.*'],
            ['Customer Receipts', 'wallet2', 'customer-receipts.index', 'customer_receipts.view', 'customer-receipts.*'],
        ],
        'Accounts' => [
            ['Expenses', 'journal-minus', 'expenses.index', 'expenses.view', 'expenses.*'],
            ['Cash & Bank', 'bank', 'accounts.index', 'accounts.view', 'accounts.*'],
        ],
        'Reports' => [
            ['Reports', 'bar-chart-line', 'reports.index', null, 'reports.*'],
        ],
        'Masters' => [
            ['Crops & Varieties', 'flower1', 'crops.index', 'masters.manage', 'crops.*'],
            ['Warehouses', 'house-door', 'warehouses.index', 'masters.manage', 'warehouses.*'],
            ['Expense Categories', 'tags', 'expense-categories.index', 'masters.manage', 'expense-categories.*'],
        ],
        'Administration' => [
            ['Users', 'person-gear', 'users.index', 'users.manage', 'users.*'],
            ['Roles & Permissions', 'shield-lock', 'roles.index', 'roles.manage', 'roles.*'],
            ['Website Content', 'globe2', 'website.admin.index', 'website.manage', 'website.admin.*'],
            ['Settings', 'gear', 'settings.edit', 'settings.manage', 'settings.*'],
            ['Audit Logs', 'clock-history', 'audit-logs.index', 'audit.view', 'audit-logs.*'],
            ['Backup & Maintenance', 'hdd-stack', 'maintenance.index', 'backup.view', 'maintenance.*'],
        ],
    ];

    /** Reports are visible if the user holds any reports.* permission. */
    private const REPORT_PERMISSIONS = [
        'reports.purchase', 'reports.sales', 'reports.inventory', 'reports.finance', 'reports.activity',
    ];

    public static function for(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $menu = [];
        foreach (self::SECTIONS as $sectionKey => $items) {
            $visible = [];
            foreach ($items as [$label, $icon, $route, $permission, $pattern]) {
                if (! Route::has($route)) {
                    continue;
                }
                $allowed = $permission === null
                    ? collect(self::REPORT_PERMISSIONS)->contains(fn ($p) => $user->hasPermission($p))
                    : $user->hasPermission($permission);
                if ($allowed) {
                    $translatedLabel = __('menu.items.'.$label);
                    if ($translatedLabel === 'menu.items.'.$label) {
                        $translatedLabel = $label;
                    }
                    $visible[] = [
                        'label' => $translatedLabel,
                        'raw_key' => $label,
                        'icon' => $icon,
                        'route' => $route,
                        'pattern' => $pattern,
                    ];
                }
            }
            if ($visible) {
                $translatedSection = __('menu.sections.'.$sectionKey);
                if ($translatedSection === 'menu.sections.'.$sectionKey) {
                    $translatedSection = $sectionKey;
                }
                $menu[$translatedSection] = $visible;
            }
        }

        return $menu;
    }
}
