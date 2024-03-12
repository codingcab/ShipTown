<?php

/*
|--------------------------------------------------------------------------
| User Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Admin\Settings\Modules\Webhooks\SubscriptionController;
use App\Http\Controllers\Auth;
use App\Http\Controllers\MailTemplatePreviewController;
use App\Http\Controllers\Order;
use App\Http\Controllers\Csv;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataCollectorController;
use App\Http\Controllers\PdfOrderController;
use App\Http\Controllers\ProductsMergeController;
use App\Http\Controllers\Reports;
use App\Http\Controllers\ShippingLabelController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'dashboard');
Route::redirect('home', '/')->name('home');

Route::resource('verify', Auth\TwoFactorController::class)->only(['index', 'store']);

Route::view('quick-connect/magento', 'quick-connect.magento')->name('quick-connect.magento');

Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('fulfillment-dashboard', [DashboardController::class, 'index'])->name('fulfillment-dashboard');
Route::get('inventory-dashboard', [Reports\InventoryDashboardController::class, 'index'])->name('inventory-dashboard');
Route::get('products-merge', [ProductsMergeController::class, 'index'])->name('products-merge');
Route::view('fulfillment-statistics', 'fulfillment-statistics')->name('fulfillment-statistics');
Route::view('products', 'products')->name('products');
Route::view('picklist', 'picklist')->name('picklist');
Route::view('orders', 'orders')->name('orders');
Route::view('stocktaking', 'stocktaking')->name('stocktaking');
Route::view('setting-profile', 'setting-profile')->name('setting-profile');
Route::view('data-collector', 'data-collector-list')->name('data-collector');
Route::get('data-collector/{data_collection_id}', [DataCollectorController::class, 'index'])->name('data-collector-show');
Route::get('shipping-labels/{shipping_label}', [ShippingLabelController::class, 'show'])->name('shipping-labels');
Route::view('autopilot/packlist', 'autopilot/packlist')->name('autopilot.packlist');
Route::resource('order/packsheet', Order\PacksheetController::class)->only(['show']);

Route::name('reports.')->group(function () {
    Route::resource('reports/stocktake-suggestions', Reports\StocktakeSuggestionsController::class)->only('index');
    Route::get('reports/inventory-dashboard', [Reports\InventoryDashboardController::class, 'index'])->name('inventory-dashboard');
    Route::get('reports/picks', [Reports\PickController::class, 'index'])->name('picks');
    Route::get('reports/shipments', [Reports\ShipmentController::class, 'index'])->name('shipments');
    Route::get('reports/inventory', [Reports\InventoryController::class, 'index'])->name('inventory');
    Route::get('reports/restocking', [Reports\RestockingReportController::class, 'index'])->name('restocking');
    Route::view('reports/inventory-movements', 'reports/inventory-movements')->name('inventory-movements');
    Route::get('reports/stocktake-suggestions-totals', [Reports\StocktakeSuggestionsTotalsReportController::class, 'index'])->name('stocktake-suggestions-totals');
    Route::get('reports/inventory-movements-summary', [Reports\InventoryMovementsSummaryController::class, 'index'])->name('inventory-movements-summary');
});

Route::get('pdf/orders/{order_number}/{template}', [PdfOrderController::class, 'show']);
Route::get('csv/ready_order_shipments', [Csv\ReadyOrderShipmentController::class, 'index'])->name('ready_order_shipments_as_csv');
Route::get('csv/order_shipments', [Csv\PartialOrderShipmentController::class, 'index'])->name('partial_order_shipments_as_csv');
Route::get('csv/products/picked', [Csv\ProductsPickedInWarehouse::class, 'index'])->name('warehouse_picks.csv');
Route::get('csv/products/shipped', [Csv\ProductsShippedFromWarehouseController::class, 'index'])->name('warehouse_shipped.csv');
Route::get('csv/boxtop/stock', [Csv\BoxTopStockController::class, 'index'])->name('boxtop-warehouse-stock.csv');
Route::view('settings/warehouses', 'settings/warehouses')->name('settings.warehouses');

Route::middleware(['role:admin'])->group(function () {
    Route::view('settings/modules/magento2msi', 'settings/modules/magento2msi');
    Route::view('settings/modules/stocktake-suggestions', 'settings/modules/stocktake-suggestions');
    Route::view('settings/modules/active-orders-inventory-reservations', 'settings/modules/active-orders-inventory-reservations');

    Route::view('admin/settings', 'settings')->name('settings');
    Route::view('admin/activity-log', 'activity-log')->name('activity-log');
    Route::view('admin/settings/general', 'settings/general')->name('settings.general');
    Route::view('admin/settings/order-statuses', 'settings/order-statuses')->name('settings.order_statuses');
    Route::view('admin/settings/printnode', 'settings/printnode')->name('settings.printnode');
    Route::view('admin/settings/rmsapi', 'settings/rmsapi')->name('settings.rmsapi');
    Route::view('admin/settings/dpd-ireland', 'settings/dpd-ireland')->name('settings.dpd-ireland');
    Route::view('admin/settings/api2cart', 'settings/api2cart')->name('settings.api2cart');
    Route::view('admin/settings/api', 'settings/api')->name('settings.api');
    Route::view('admin/settings/users', 'settings/users')->name('settings.users');
    Route::view('admin/settings/mail-templates', 'settings/mail-templates')->name('settings.mail_templates');
    Route::get('admin/settings/mail-templates/{mailTemplate}/preview', [MailTemplatePreviewController::class, 'index'])->name('settings.mail_template_preview');
    Route::view('admin/settings/navigation-menu', 'settings/navigation-menu')->name('settings.navigation_menu');
    Route::view('admin/settings/automations', 'settings/automations')->name('settings.automations');
    Route::view('admin/settings/modules', 'settings/modules')->name('settings.modules');
    Route::view('admin/settings/modules/dpd-uk', 'settings/dpd-uk')->name('settings.modules.dpd-uk');
    Route::get('admin/settings/modules/webhooks/subscriptions', [SubscriptionController::class, 'index'])->name('webhooks::subscriptions');
    Route::view('admin/modules/slack/config', 'modules/slack/config')->name('modules.slack.config');
    Route::view('admin/settings/modules/magento-api', 'settings/magento-api')->name('settings.modules.magento-api');
});
