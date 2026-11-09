<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Business Website
|--------------------------------------------------------------------------
*/
Route::get('/', [\App\Http\Controllers\Website\PublicWebsiteController::class, 'home'])->name('home');
Route::get('/about', [\App\Http\Controllers\Website\PublicWebsiteController::class, 'about'])->name('website.about');
Route::get('/crop-procurement', [\App\Http\Controllers\Website\PublicWebsiteController::class, 'procurement'])->name('website.procurement');
Route::get('/products', [\App\Http\Controllers\Website\PublicWebsiteController::class, 'products'])->name('website.products');
Route::get('/farmer-registration', [\App\Http\Controllers\Website\PublicWebsiteController::class, 'farmerRegister'])->name('website.farmer.register');
Route::post('/farmer-registration', [\App\Http\Controllers\Website\PublicWebsiteController::class, 'farmerRegisterStore'])->middleware('throttle:public-forms')->name('website.farmer.register.store');
Route::get('/contact', [\App\Http\Controllers\Website\PublicWebsiteController::class, 'contact'])->name('website.contact');
Route::post('/contact', [\App\Http\Controllers\Website\PublicWebsiteController::class, 'inquiryStore'])->middleware('throttle:public-forms')->name('website.contact.store');
Route::get('/privacy-policy', [\App\Http\Controllers\Website\PublicWebsiteController::class, 'privacy'])->name('website.privacy');
Route::get('/terms-of-trade', [\App\Http\Controllers\Website\PublicWebsiteController::class, 'terms'])->name('website.terms');
Route::get('/sitemap.xml', [\App\Http\Controllers\Website\PublicWebsiteController::class, 'sitemap'])->name('website.sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\Website\PublicWebsiteController::class, 'robots'])->name('website.robots');
Route::get('/locale/{locale}', [\App\Http\Controllers\LocaleController::class, 'switch'])->name('locale.switch');
Route::post('/locale/{locale}', [\App\Http\Controllers\LocaleController::class, 'switch'])->name('locale.switch.post');
Route::view('/offline', 'offline')->name('offline');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::get('/csrf-token', fn () => response()->json(['token' => csrf_token()]))->name('csrf.refresh');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login')->name('login.attempt');

    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:public-forms')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:public-forms')->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| ERP (authenticated). Every route is protected by a permission check on the
| server — hiding a menu item is never the only protection.
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'auth.session', 'session.secure', 'password.fresh'])->prefix('erp')->group(function () {

    Route::get('/change-password', [ChangePasswordController::class, 'edit'])->name('password.change');
    Route::put('/change-password', [ChangePasswordController::class, 'update'])->name('password.change.update');

    Route::get('/dashboard', DashboardController::class)->middleware('can:dashboard.view')->name('dashboard');

    // ---- Masters --------------------------------------------------------
    Route::middleware('can:masters.manage')->group(function () {
        Route::get('crops/export', [\App\Http\Controllers\Masters\CropController::class, 'export'])->name('crops.export');
        Route::resource('crops', \App\Http\Controllers\Masters\CropController::class);
        Route::patch('crops/{crop}/toggle', [\App\Http\Controllers\Masters\CropController::class, 'toggle'])->name('crops.toggle');
        Route::post('crops/{crop}/varieties', [\App\Http\Controllers\Masters\CropController::class, 'storeVariety'])->name('crops.varieties.store');
        Route::put('crops/{crop}/varieties/{variety}', [\App\Http\Controllers\Masters\CropController::class, 'updateVariety'])->name('crops.varieties.update');

        Route::get('warehouses/export', [\App\Http\Controllers\Masters\WarehouseController::class, 'export'])->name('warehouses.export');
        Route::resource('warehouses', \App\Http\Controllers\Masters\WarehouseController::class)->except(['show', 'destroy']);
        Route::patch('warehouses/{warehouse}/toggle', [\App\Http\Controllers\Masters\WarehouseController::class, 'toggle'])->name('warehouses.toggle');

        Route::get('expense-categories/export', [\App\Http\Controllers\Accounts\ExpenseCategoryController::class, 'export'])->name('expense-categories.export');
        Route::resource('expense-categories', \App\Http\Controllers\Accounts\ExpenseCategoryController::class)->only(['index', 'store', 'update']);
        Route::patch('expense-categories/{expenseCategory}/toggle', [\App\Http\Controllers\Accounts\ExpenseCategoryController::class, 'toggle'])->name('expense-categories.toggle');
    });

    // ---- Farmers --------------------------------------------------------
    Route::middleware('can:farmers.view')->group(function () {
        Route::get('farmers', [\App\Http\Controllers\Farmers\FarmerController::class, 'index'])->name('farmers.index');
        Route::get('farmers/export', [\App\Http\Controllers\Farmers\FarmerController::class, 'export'])->name('farmers.export');
        Route::get('farmers/{farmer}', [\App\Http\Controllers\Farmers\FarmerController::class, 'show'])->name('farmers.show');
        Route::get('farmers/{farmer}/print', [\App\Http\Controllers\Farmers\FarmerController::class, 'print'])->name('farmers.print');

        Route::middleware('can:farmers.create')->group(function () {
            Route::get('farmers-create', [\App\Http\Controllers\Farmers\FarmerController::class, 'create'])->name('farmers.create');
            Route::post('farmers', [\App\Http\Controllers\Farmers\FarmerController::class, 'store'])->name('farmers.store');
        });

        Route::middleware('can:farmers.edit')->group(function () {
            Route::get('farmers/{farmer}/edit', [\App\Http\Controllers\Farmers\FarmerController::class, 'edit'])->name('farmers.edit');
            Route::put('farmers/{farmer}', [\App\Http\Controllers\Farmers\FarmerController::class, 'update'])->name('farmers.update');
            Route::patch('farmers/{farmer}/toggle', [\App\Http\Controllers\Farmers\FarmerController::class, 'toggle'])->name('farmers.toggle');
        });
    });

    // ---- Customers & Mills ----------------------------------------------
    Route::middleware('can:customers.view')->group(function () {
        Route::get('customers', [\App\Http\Controllers\Customers\CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/export', [\App\Http\Controllers\Customers\CustomerController::class, 'export'])->name('customers.export');
        Route::get('customers/{customer}', [\App\Http\Controllers\Customers\CustomerController::class, 'show'])->name('customers.show');
        Route::get('customers/{customer}/print', [\App\Http\Controllers\Customers\CustomerController::class, 'print'])->name('customers.print');

        Route::middleware('can:customers.manage')->group(function () {
            Route::get('customers-create', [\App\Http\Controllers\Customers\CustomerController::class, 'create'])->name('customers.create');
            Route::post('customers', [\App\Http\Controllers\Customers\CustomerController::class, 'store'])->name('customers.store');
            Route::get('customers/{customer}/edit', [\App\Http\Controllers\Customers\CustomerController::class, 'edit'])->name('customers.edit');
            Route::put('customers/{customer}', [\App\Http\Controllers\Customers\CustomerController::class, 'update'])->name('customers.update');
            Route::patch('customers/{customer}/toggle', [\App\Http\Controllers\Customers\CustomerController::class, 'toggle'])->name('customers.toggle');
        });
    });

    // ---- Purchases -----------------------------------------------------
    Route::middleware('can:purchases.view')->group(function () {
        Route::get('purchases', [\App\Http\Controllers\Procurement\PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('purchases/{purchase}', [\App\Http\Controllers\Procurement\PurchaseController::class, 'show'])->name('purchases.show');
        Route::get('purchases/{purchase}/receipt', [\App\Http\Controllers\Procurement\PurchaseController::class, 'receipt'])->name('purchases.receipt');
        Route::get('purchases/{purchase}/pdf', [\App\Http\Controllers\Procurement\PurchaseController::class, 'pdf'])->name('purchases.pdf');

        Route::middleware('can:purchases.create')->group(function () {
            Route::get('purchases-create', [\App\Http\Controllers\Procurement\PurchaseController::class, 'create'])->name('purchases.create');
            Route::post('purchases', [\App\Http\Controllers\Procurement\PurchaseController::class, 'store'])->name('purchases.store');
            Route::get('purchases/{purchase}/edit', [\App\Http\Controllers\Procurement\PurchaseController::class, 'edit'])->name('purchases.edit');
            Route::put('purchases/{purchase}', [\App\Http\Controllers\Procurement\PurchaseController::class, 'update'])->name('purchases.update');
        });

        Route::post('purchases/{purchase}/finalize', [\App\Http\Controllers\Procurement\PurchaseController::class, 'finalize'])
            ->middleware('can:purchases.finalize')
            ->name('purchases.finalize');

        Route::post('purchases/{purchase}/reverse', [\App\Http\Controllers\Procurement\PurchaseController::class, 'reverse'])
            ->middleware('can:purchases.reverse')
            ->name('purchases.reverse');
    });

    // ---- Farmer Payments ------------------------------------------------
    Route::middleware('can:farmer_payments.view')->group(function () {
        Route::get('farmer-payments', [\App\Http\Controllers\Procurement\FarmerPaymentController::class, 'index'])->name('farmer-payments.index');
        Route::get('farmer-payments/{payment}', [\App\Http\Controllers\Procurement\FarmerPaymentController::class, 'show'])->name('farmer-payments.show');
        Route::get('farmer-payments/{payment}/print', [\App\Http\Controllers\Procurement\FarmerPaymentController::class, 'print'])->name('farmer-payments.print');

        Route::middleware('can:farmer_payments.create')->group(function () {
            Route::get('farmer-payments-create', [\App\Http\Controllers\Procurement\FarmerPaymentController::class, 'create'])->name('farmer-payments.create');
            Route::post('farmer-payments', [\App\Http\Controllers\Procurement\FarmerPaymentController::class, 'store'])->name('farmer-payments.store');
        });

        Route::post('farmer-payments/{payment}/reverse', [\App\Http\Controllers\Procurement\FarmerPaymentController::class, 'reverse'])
            ->middleware('can:payments.reverse')
            ->name('farmer-payments.reverse');
    });

    // ---- Inventory & Stock ----------------------------------------------
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('inventory', [\App\Http\Controllers\Inventory\InventoryController::class, 'index'])->name('inventory.index');
        Route::get('inventory/movements', [\App\Http\Controllers\Inventory\InventoryController::class, 'movements'])->name('inventory.movements');

        // Stock Transfers
        Route::get('stock-transfers', [\App\Http\Controllers\Inventory\StockTransferController::class, 'index'])->name('stock-transfers.index');
        Route::get('stock-transfers/{transfer}', [\App\Http\Controllers\Inventory\StockTransferController::class, 'show'])->name('stock-transfers.show');
        Route::get('stock-transfers/{transfer}/challan', [\App\Http\Controllers\Inventory\StockTransferController::class, 'challan'])->name('stock-transfers.challan');
        Route::get('stock-transfers/{transfer}/challan/pdf', [\App\Http\Controllers\Inventory\StockTransferController::class, 'challanPdf'])->name('stock-transfers.challan.pdf');

        Route::middleware('can:inventory.transfer')->group(function () {
            Route::get('stock-transfers-create', [\App\Http\Controllers\Inventory\StockTransferController::class, 'create'])->name('stock-transfers.create');
            Route::post('stock-transfers', [\App\Http\Controllers\Inventory\StockTransferController::class, 'store'])->name('stock-transfers.store');
        });

        // Stock Adjustments
        Route::get('stock-adjustments', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
        Route::get('stock-adjustments/{stockAdjustment}', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'show'])->name('stock-adjustments.show');

        Route::middleware('can:inventory.adjust')->group(function () {
            Route::get('stock-adjustments-create', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
            Route::post('stock-adjustments', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
        });

        Route::middleware('can:inventory.approve')->group(function () {
            Route::post('stock-adjustments/{stockAdjustment}/approve', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'approve'])->name('stock-adjustments.approve');
            Route::post('stock-adjustments/{stockAdjustment}/reject', [\App\Http\Controllers\Inventory\StockAdjustmentController::class, 'reject'])->name('stock-adjustments.reject');
        });
    });

    // ---- Sales & Dispatch ----------------------------------------------
    Route::middleware('can:sales.view')->group(function () {
        // Sales Orders
        Route::get('sales-orders', [\App\Http\Controllers\Sales\SalesOrderController::class, 'index'])->name('sales-orders.index');
        Route::get('sales-orders/export', [\App\Http\Controllers\Sales\SalesOrderController::class, 'export'])->name('sales-orders.export');
        Route::get('sales-orders/{salesOrder}', [\App\Http\Controllers\Sales\SalesOrderController::class, 'show'])->name('sales-orders.show');
        Route::get('sales-orders/{salesOrder}/print', [\App\Http\Controllers\Sales\SalesOrderController::class, 'print'])->name('sales-orders.print');

        Route::middleware('can:sales.create')->group(function () {
            Route::get('sales-orders-create', [\App\Http\Controllers\Sales\SalesOrderController::class, 'create'])->name('sales-orders.create');
            Route::post('sales-orders', [\App\Http\Controllers\Sales\SalesOrderController::class, 'store'])->name('sales-orders.store');
        });

        Route::post('sales-orders/{salesOrder}/mark-completed', [\App\Http\Controllers\Sales\SalesOrderController::class, 'markCompleted'])
            ->middleware('can:sales.create')
            ->name('sales-orders.mark-completed');

        // Sales Invoices
        Route::get('sales-invoices', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'index'])->name('sales-invoices.index');
        Route::get('sales-invoices/export', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'export'])->name('sales-invoices.export');
        Route::get('sales-invoices/{salesInvoice}', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'show'])->name('sales-invoices.show');
        Route::get('sales-invoices/{salesInvoice}/print', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'print'])->name('sales-invoices.print');
        Route::get('sales-invoices/{salesInvoice}/pdf', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'pdf'])->name('sales-invoices.pdf');
        Route::get('sales-invoices/{salesInvoice}/challan', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'challan'])->name('sales-invoices.challan');
        Route::get('sales-invoices/{salesInvoice}/challan/pdf', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'challanPdf'])->name('sales-invoices.challan.pdf');

        Route::middleware('can:sales.create')->group(function () {
            Route::get('sales-invoices-create', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'create'])->name('sales-invoices.create');
            Route::post('sales-invoices', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'store'])->name('sales-invoices.store');
        });

        Route::post('sales-invoices/{salesInvoice}/finalize', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'finalize'])
            ->middleware('can:sales.finalize')
            ->name('sales-invoices.finalize');

        Route::post('sales-invoices/{salesInvoice}/mark-delivered', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'markDelivered'])
            ->middleware('can:sales.finalize')
            ->name('sales-invoices.mark-delivered');

        Route::post('sales-invoices/{salesInvoice}/revert-delivery', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'revertDelivery'])
            ->middleware('can:sales.finalize')
            ->name('sales-invoices.revert-delivery');

        Route::post('sales-invoices/{salesInvoice}/reverse', [\App\Http\Controllers\Sales\SalesInvoiceController::class, 'reverse'])
            ->middleware('can:sales.reverse')
            ->name('sales-invoices.reverse');
    });

    // ---- Customer Receipts ----------------------------------------------
    Route::middleware('can:customer_receipts.view')->group(function () {
        Route::get('customer-receipts', [\App\Http\Controllers\Sales\CustomerReceiptController::class, 'index'])->name('customer-receipts.index');
        Route::get('customer-receipts/export', [\App\Http\Controllers\Sales\CustomerReceiptController::class, 'export'])->name('customer-receipts.export');
        Route::get('customer-receipts/{customerReceipt}', [\App\Http\Controllers\Sales\CustomerReceiptController::class, 'show'])->name('customer-receipts.show');
        Route::get('customer-receipts/{customerReceipt}/print', [\App\Http\Controllers\Sales\CustomerReceiptController::class, 'print'])->name('customer-receipts.print');

        Route::middleware('can:customer_receipts.create')->group(function () {
            Route::get('customer-receipts-create', [\App\Http\Controllers\Sales\CustomerReceiptController::class, 'create'])->name('customer-receipts.create');
            Route::post('customer-receipts', [\App\Http\Controllers\Sales\CustomerReceiptController::class, 'store'])->name('customer-receipts.store');
        });

        Route::post('customer-receipts/{customerReceipt}/reverse', [\App\Http\Controllers\Sales\CustomerReceiptController::class, 'reverse'])
            ->middleware('can:payments.reverse')
            ->name('customer-receipts.reverse');
    });

    // ---- Accounts & Finance ---------------------------------------------
    Route::middleware('can:expenses.view')->group(function () {
        Route::get('expenses', [\App\Http\Controllers\Accounts\ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/export', [\App\Http\Controllers\Accounts\ExpenseController::class, 'export'])->name('expenses.export');
        Route::get('expenses/{expense}', [\App\Http\Controllers\Accounts\ExpenseController::class, 'show'])->name('expenses.show');

        Route::middleware('can:expenses.create')->group(function () {
            Route::get('expenses-create', [\App\Http\Controllers\Accounts\ExpenseController::class, 'create'])->name('expenses.create');
            Route::post('expenses', [\App\Http\Controllers\Accounts\ExpenseController::class, 'store'])->name('expenses.store');
        });

        Route::post('expenses/{expense}/reverse', [\App\Http\Controllers\Accounts\ExpenseController::class, 'reverse'])
            ->middleware('can:expenses.approve')
            ->name('expenses.reverse');
    });

    Route::middleware('can:accounts.view')->group(function () {
        Route::get('accounts', [\App\Http\Controllers\Accounts\CashAccountController::class, 'index'])->name('accounts.index');
        Route::get('accounts/export', [\App\Http\Controllers\Accounts\CashAccountController::class, 'export'])->name('accounts.export');
        Route::get('daybook/export', [\App\Http\Controllers\Accounts\DaybookController::class, 'export'])->name('accounts.daybook.export');
        Route::get('daybook', [\App\Http\Controllers\Accounts\DaybookController::class, 'daybook'])->name('accounts.daybook');
        Route::get('profit-and-loss/export', [\App\Http\Controllers\Accounts\DaybookController::class, 'exportProfitAndLoss'])->name('accounts.profit_and_loss.export');
        Route::get('profit-and-loss', [\App\Http\Controllers\Accounts\DaybookController::class, 'profitAndLoss'])->name('accounts.profit_and_loss');

        Route::middleware('can:accounts.manage')->group(function () {
            Route::post('accounts', [\App\Http\Controllers\Accounts\CashAccountController::class, 'store'])->name('accounts.store');
            Route::post('accounts/transfer', [\App\Http\Controllers\Accounts\CashAccountController::class, 'transfer'])->name('accounts.transfer');
        });
    });

    // ---- Administration -------------------------------------------------
    Route::middleware('can:users.manage')->group(function () {
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
    });

    Route::middleware('can:roles.manage')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });

    Route::middleware('can:settings.manage')->group(function () {
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::post('settings/test-email', [SettingsController::class, 'sendTestEmail'])->name('settings.test_email');
        Route::post('settings/test-whatsapp', [SettingsController::class, 'sendTestWhatsApp'])->name('settings.test_whatsapp');
        Route::put('settings/{tab}', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware('can:website.manage')->group(function () {
        Route::get('website-content', [\App\Http\Controllers\Admin\WebsiteCmsController::class, 'index'])->name('website.admin.index');
        Route::put('website-content', [\App\Http\Controllers\Admin\WebsiteCmsController::class, 'update'])->name('website.admin.update');
        Route::put('website-crop-rates', [\App\Http\Controllers\Admin\WebsiteCmsController::class, 'updateCropRates'])->name('website.admin.crop-rates.update');
        Route::get('website-inquiries', [\App\Http\Controllers\Admin\WebsiteCmsController::class, 'inquiries'])->name('website.admin.inquiries');
        Route::patch('website-inquiries/{websiteInquiry}/toggle', [\App\Http\Controllers\Admin\WebsiteCmsController::class, 'toggleInquiryStatus'])->name('website.admin.inquiries.toggle');
    });

    Route::middleware('can:audit.view')->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    });

    // --- Phase 8: Reports & Analytics ---
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Reports\ReportController::class, 'index'])->name('index');
        Route::get('purchases', [\App\Http\Controllers\Reports\ReportController::class, 'purchases'])->name('purchases');
        Route::get('sales', [\App\Http\Controllers\Reports\ReportController::class, 'sales'])->name('sales');
        Route::get('inventory', [\App\Http\Controllers\Reports\ReportController::class, 'inventory'])->name('inventory');
        Route::get('finance', [\App\Http\Controllers\Reports\ReportController::class, 'finance'])->name('finance');
        Route::get('audit', [\App\Http\Controllers\Reports\ReportController::class, 'audit'])->name('audit');
    });

    // --- Phase 8: Backup & Maintenance ---
    Route::middleware('can:backup.view')->prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\MaintenanceController::class, 'index'])->name('index');
        Route::post('backup', [\App\Http\Controllers\Admin\MaintenanceController::class, 'createBackup'])->name('backup.create');
        Route::get('backup/{filename}', [\App\Http\Controllers\Admin\MaintenanceController::class, 'downloadBackup'])->name('backup.download');
        Route::delete('backup/{filename}', [\App\Http\Controllers\Admin\MaintenanceController::class, 'deleteBackup'])->name('backup.delete');
    });
});

