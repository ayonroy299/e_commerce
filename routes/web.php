<?php

use App\Utils\CrudRouter;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BaseUnitController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\TodoController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PosOrderController;
use App\Http\Controllers\Admin\PosSessionController;
use App\Http\Controllers\Frontend\WelcomePageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockMovementController;

require_once __DIR__ . '/auth.php';

Route::get('/', WelcomePageController::class)->name('welcome');

// AUTH & VERIFIED

Route::middleware(['auth', 'verified', \App\Http\Middleware\BranchContextMiddleware::class])->prefix('admin')->group(function () {
    CrudRouter::setFor('products', ProductController::class);
    Route::get('/admin/products/{product}/edit-data', [ProductController::class, 'editData'])
        ->name('products.edit-data');
    Route::get('/products/create', [ProductController::class, 'create'])
        ->name('products.create');

    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
        ->name('products.edit');


    CrudRouter::setFor('taxes', App\Http\Controllers\TaxController::class);
    CrudRouter::setFor('categories', CategoryController::class);
    CrudRouter::setFor('tags', TagController::class);
    CrudRouter::setFor('brands', BrandController::class);
    CrudRouter::setFor('sub-categories', SubCategoryController::class);
    CrudRouter::setFor('payment-methods', PaymentMethodController::class);
    CrudRouter::setFor('todos', TodoController::class);
    CrudRouter::setFor('tasks', TaskController::class);
    CrudRouter::setFor('users', UserController::class);
    Route::post('/switch-branch', [UserController::class, 'switch'])
        ->name('branch.switch');
    CrudRouter::setFor('branches', BranchController::class);
    CrudRouter::setFor('warehouses', WarehouseController::class);
    CrudRouter::setFor('suppliers', SupplierController::class);
    CrudRouter::setFor('base-units', BaseUnitController::class);
    CrudRouter::setFor('units', UnitController::class);
    CrudRouter::setFor('expense-categories', ExpenseCategoryController::class);
    CrudRouter::setFor('expenses', App\Http\Controllers\ExpenseController::class);

    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    CrudRouter::setFor('currencies', App\Http\Controllers\CurrencyController::class);
    CrudRouter::setFor('customers', App\Http\Controllers\CustomerController::class);

    Route::get('/settings', [SettingController::class, 'general'])->name('settings.general');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');


    // stock 
    Route::get('admin/products/{product}/stock-move', [StockMovementController::class, 'create'])
        ->name('admin.stock.move.form');

    Route::post('admin/stock/move', [StockMovementController::class, 'store'])
        ->name('admin.stock.move');



    // =======================
    // POS
    // =======================
    Route::get('/pos', [PosOrderController::class, 'index'])
        ->name('pos.index');
    Route::get('/pos/customers/search', [PosOrderController::class, 'customerSearch'])
        ->name('pos.customers.search');

    // -----------------------
    // POS Session
    // -----------------------
    Route::get('/pos/session/current', [PosSessionController::class, 'current'])
        ->name('pos.session.current');

    Route::post('/pos/session/open', [PosSessionController::class, 'open'])
        ->name('pos.session.open');

    Route::post('/pos/session/close', [PosSessionController::class, 'close'])
        ->name('pos.session.close');

    // -----------------------
    // POS Orders
    // -----------------------
    Route::post('/pos/orders', [PosOrderController::class, 'store'])
        ->name('pos.orders.store');

    // invoice MUST be before index
    Route::get('/pos/orders/{order}/invoice', [PosOrderController::class, 'invoice'])
        ->name('pos.orders.invoice');

    // orders list (sales history)
    Route::get('/pos/orders', [PosOrderController::class, 'orders'])
        ->name('pos.orders.index');

    Route::post('/pos/orders/{order}/void', [PosOrderController::class, 'void'])
        ->name('pos.orders.void');
    Route::post('/pos/orders/{order}/complete', [PosOrderController::class, 'completeDraft'])
        ->name('pos.orders.complete');
    Route::post('/pos/orders/{order}/payments', [PosOrderController::class, 'addPayment'])
        ->name('pos.orders.payments.store');




    // Route::post('/pos/orders/{order}/refund', [PosRefundController::class, 'refund'])->name('pos.orders.refund');
    // Route::get('/pos/report/daily', [PosReportController::class, 'daily'])->name('pos.report.daily');










    // Stock Adjustments
    Route::resource('adjustments', App\Http\Controllers\Admin\StockAdjustmentController::class);
    Route::post('adjustments/{adjustment}/approve', [App\Http\Controllers\Admin\StockAdjustmentController::class, 'approve'])
        ->name('adjustments.approve');
    // Inventory & Stock Transfers
    CrudRouter::setFor('stock-transfers', App\Http\Controllers\Admin\StockTransferController::class);
    Route::post('stock-transfers/{transfer}/send', [App\Http\Controllers\Admin\StockTransferController::class, 'send'])->name('stock-transfers.send');
    Route::post('stock-transfers/{transfer}/receive', [App\Http\Controllers\Admin\StockTransferController::class, 'receive'])->name('stock-transfers.receive');

    // EMI
    CrudRouter::setFor('emi-plans', App\Http\Controllers\Admin\EmiPlanController::class);
    Route::get('emi/contracts', [App\Http\Controllers\Admin\EmiContractController::class, 'index'])->name('emi-contracts.index');
    Route::get('emi/contracts/{contract}', [App\Http\Controllers\Admin\EmiContractController::class, 'show'])->name('emi-contracts.show');
    Route::post('emi/contracts/{contract}/cancel', [App\Http\Controllers\Admin\EmiContractController::class, 'cancel'])->name('emi-contracts.cancel');
    Route::post('emi/receipts', [App\Http\Controllers\Admin\EmiReceiptController::class, 'store'])->name('emi-receipts.store');

    // Service & Warranty
    CrudRouter::setFor('service-tickets', App\Http\Controllers\Admin\ServiceTicketController::class);
    Route::post('service-tickets/{ticket}/status', [App\Http\Controllers\Admin\ServiceTicketController::class, 'updateStatus'])->name('service-tickets.update-status');
    Route::post('service-tickets/{ticket}/actions', [App\Http\Controllers\Admin\ServiceActionController::class, 'store'])->name('service-actions.store');

    // Accounting Lite
    Route::get('accounting/overview', [App\Http\Controllers\Admin\JournalController::class, 'overview'])->name('accounting.overview');
    CrudRouter::setFor('accounts', App\Http\Controllers\Admin\AccountController::class);
    Route::get('journals', [App\Http\Controllers\Admin\JournalController::class, 'index'])->name('journals.index');
    Route::get('accounts/{account}/ledger', [App\Http\Controllers\Admin\JournalController::class, 'ledger'])->name('accounts.ledger');

    // Purchasing
    Route::post('purchase-orders/{order}/approve', [App\Http\Controllers\Admin\PurchaseOrderController::class, 'approve'])
        ->name('purchase-orders.approve');
    Route::resource('purchase-orders', App\Http\Controllers\Admin\PurchaseOrderController::class);
    
    Route::get('purchase-orders/{order}/receive', [App\Http\Controllers\Admin\GrnController::class, 'create'])
        ->name('purchase-orders.receive');
    Route::post('purchase-orders/{order}/receive', [App\Http\Controllers\Admin\GrnController::class, 'store'])
        ->name('purchase-orders.receive.store');

    // Sales & POS
    Route::resource('customers', App\Http\Controllers\Admin\CustomerController::class);
    Route::get('pos', [App\Http\Controllers\Admin\PosController::class, 'index'])->name('pos.index');
    Route::post('pos/sale', [App\Http\Controllers\Admin\PosController::class, 'store'])->name('pos.store');

    // Returns
    Route::get('sales/{sale}/return', [App\Http\Controllers\Admin\SaleReturnController::class, 'create'])->name('sales.return');
    Route::post('sales/{sale}/return', [App\Http\Controllers\Admin\SaleReturnController::class, 'store'])->name('sales.return.store');
    Route::resource('sales', App\Http\Controllers\Admin\SaleController::class)->only(['index', 'show']);

    // Expenses & Financials
    Route::resource('expense-categories', App\Http\Controllers\Admin\ExpenseCategoryController::class);
    Route::resource('expenses', App\Http\Controllers\Admin\ExpenseController::class);
    // Expenses & Financials
    Route::resource('expense-categories', App\Http\Controllers\Admin\ExpenseCategoryController::class);
    Route::resource('expenses', App\Http\Controllers\Admin\ExpenseController::class);
    Route::get('reports/financial', [App\Http\Controllers\Admin\FinancialReportController::class, 'dashboard'])->name('reports.financial');

    // Roles & Permissions
    Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);

    // Settings
    Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');
    Route::resource('taxes', App\Http\Controllers\Admin\TaxController::class)->only(['store', 'update', 'destroy']);
    Route::resource('currencies', App\Http\Controllers\Admin\CurrencyController::class)->only(['store', 'update', 'destroy']);
});
