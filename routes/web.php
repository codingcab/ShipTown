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

use App\Modules\Rmsapi\src\Jobs\ImportProductsJob;
use App\Modules\Rmsapi\src\Jobs\ImportShippingsJob;
use App\Modules\Rmsapi\src\Jobs\ProcessImportedProductRecordsJob;
use App\Modules\Rmsapi\src\Models\RmsapiConnection;
use Illuminate\Support\Facades\Route;

Route::get('orders', function () {

    // dispatch Fetch jobs for all connections
    foreach (RmsapiConnection::all() as $rmsapiConnection) {
        ImportProductsJob::dispatch($rmsapiConnection->id);
        ImportShippingsJob::dispatch($rmsapiConnection->id);
        logger('Rmsapi sync job dispatched', ['connection_id' => $rmsapiConnection->id]);
    }

    return \App\Models\Order::where(['order_number' => 'DUB-TRN-130'])->first()->shippingAddress->phone;
});

// Route 2FA
Route::resource('verify', 'Auth\TwoFactorController')->only(['index', 'store']);

Route::redirect('', 'dashboard');
Route::redirect('home', 'dashboard')->name('home');

Route::view('dashboard', 'dashboard')->name('dashboard');
Route::view('performance/dashboard', 'performance')->name('performance.dashboard');
Route::view('products', 'products')->name('products');
Route::view('picklist', 'picklist')->name('picklist');
Route::view('orders2', 'orders')->name('orders');
Route::view('stocktaking', 'stocktaking')->name('stocktaking');
Route::view('setting-profile', 'setting-profile')->name('setting-profile');

Route::get('shipping-labels/{shipping_label}', 'ShippingLabelController@show')->name('shipping-labels');

Route::view('autopilot/packlist', 'autopilot/packlist')->name('autopilot.packlist');

Route::resource('order/packsheet', 'Order\PacksheetController')->only(['show']);

Route::view('reports/picks', 'reports/picks_report')->name('reports.picks');
Route::get('reports/inventory-dashboard', 'Reports\InventoryDashboardController@index')->name('reports.inventory-dashboard');
Route::get('reports/shipments', 'Reports\ShipmentController@index')->name('reports.shipments');
Route::get('reports/inventory', 'Reports\InventoryController@index')->name('reports.inventory');
Route::get('reports/restocking', 'Reports\RestockingReportController@index')->name('reports.restocking');
Route::get('reports/stocktakes', 'Reports\StocktakeController@index')->name('reports.stocktakes');

Route::get('pdf/orders/{order_number}/{template}', 'PdfOrderController@show');
Route::get('orders/{order_number}/kick', 'OrderKickController@index');
Route::get('products/last24h/kick', 'Products24hKickController@index');
Route::get('products/{sku}/kick', 'ProductKickController@index');
Route::get('csv/ready_order_shipments', 'Csv\ReadyOrderShipmentController@index')->name('ready_order_shipments_as_csv');
Route::get('csv/order_shipments', 'Csv\PartialOrderShipmentController@index')->name('partial_order_shipments_as_csv');
Route::get('csv/products/picked', 'Csv\ProductsPickedInWarehouse@index')->name('warehouse_picks.csv');
Route::get('csv/products/shipped', 'Csv\ProductsShippedFromWarehouseController@index')->name('warehouse_shipped.csv');
Route::get('csv/boxtop/stock', 'Csv\BoxTopStockController@index')->name('boxtop-warehouse-stock.csv');
