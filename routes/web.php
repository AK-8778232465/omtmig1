<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderCreationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\OrderFormController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Auth\LoginController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes();

Route::middleware('auth:web')->controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('home', 'index')->name('home');
    Route::get('profile_Edit', 'profileEdit')->name('profile_Edit');
    Route::post('profile_Update', 'profileupdate')->name('profile_Update');
    Route::post('getCounty', 'getCounty')->name('getCounty');
    Route::any('getOrderData', 'getOrderData')->name('getOrderData');
    Route::any('dashboard_count', 'dashboard_count')->name('dashboard_count');
    Route::post('dashboard_clientwise_count', 'dashboard_clientwise_count')->name('dashboard_clientwise_count');
    Route::post('dashboard_userwise_count', 'dashboard_userwise_count')->name('dashboard_userwise_count');
    Route::post('revenue_detail', 'revenue_detail')->name('revenue_detail');
    Route::post('revenue_detail_client', 'revenue_detail_client')->name('revenue_detail_client');
    Route::post('order_detail', 'order_detail')->name('order_detail');
    Route::post('getTotalData', 'getTotalData')->name('getTotalData');
    Route::any('previous_count', 'previous_count')->name('previous_count');
    Route::any('total_users', 'total_users')->name('total_users');
    Route::any('pending_status', 'pending_status')->name('pending_status');
    Route::any('total_users_name', 'total_users_name')->name('total_users_name');
    Route::any('tat_zone_count', 'tat_zone_count')->name('tat_zone_count');
    Route::any('carry_over_monthly_count', 'carry_over_monthly_count')->name('carry_over_monthly_count');
    Route::any('resourceTable', 'resourceTable')->name('resourceTable');
    Route::any('yesterday_resourceTable', 'yesterday_resourceTable')->name('yesterday_resourceTable');

    Route::get('delete_old_resource', 'delete_old_resource')->name('delete_old_resource');







    Route::post('getTotalDataFte', 'getTotalDataFte')->name('getTotalDataFte');

    Route::post('revenue_detail_process_fte', 'revenue_detail_process_fte')->name('revenue_detail_process_fte');
    Route::post('revenue_detail_processDetail_fte', 'revenue_detail_processDetail_fte')->name('revenue_detail_processDetail_fte');
    Route::post('revenue_detail_process_total_fte', 'revenue_detail_process_total_fte')->name('revenue_detail_process_total_fte');

    Route::post('dashboard_dropdown', 'dashboard_dropdown')->name('dashboard_dropdown');
    Route::post('revenue_detail_client_fte', 'revenue_detail_client_fte')->name('revenue_detail_client_fte');

    Route::post('get_lob_dashboard', 'get_lob_dashboard')->name('get_lob_dashboard');
    Route::post('get_process_dashboard', 'get_process_dashboard')->name('get_process_dashboard');
    Route::post('get_product_dashboard', 'get_product_dashboard')->name('get_product_dashboard');

});

Route::middleware(['auth:web', 'role_or:Super Admin,PM/TL,AVP/VP,Business Head,SPOC'])->controller(SettingController::class)->group(function () {
    Route::get('settings', 'setting')->name('settings');
    Route::get('settings/users', 'setting')->name('users');
    Route::get('settings/products', 'setting')->name('products');
    Route::get('settings/sduploads', 'setting')->name('sduploads');
    Route::get('settings/geoinformations', 'setting')->name('geoinformations');
    Route::get('settings/clients', 'setting')->name('clients');

    // Users
    Route::post('/usersInsert', 'addUsers')->name('usersInsert');
    Route::post('edit_user', 'edit_user')->name('edit_user');
    Route::post('/updateUsers', 'updateUsers')->name('updateUsers');
    Route::any('/userStatus/{userid?}', 'userStatus');
    Route::post('/mappingData', 'mappingData')->name('mappingData');
    Route::any('/addMapping', 'addMapping')->name('addMapping');
    Route::post('/removeMapping', 'removeMapping')->name('removeMapping');
    Route::post('/updateMapping', 'updateMapping')->name('updateMapping');
    Route::get('/getPreviouslyAssignedIDs', 'getPreviouslyAssignedIDs')->name('getPreviouslyAssignedIDs');
    Route::post('/show_user', 'showUser')->name('show_user');
    Route::post('/getUserList', 'getUserList')->name('getUserList');
    //products
    Route::post('/productInsert', 'addproduct')->name('productInsert');
    Route::any('/show_products', 'showproduct')->name('show_products');
    Route::any('/productStatus/{productid?}', 'productStatus');
    Route::post('/edit_product', 'edit_product')->name('edit_product');
    Route::any('/update_product', 'update_product')->name('update_product');

    //Supporting doc's
    Route::any('/getlobId', 'getlobId')->name('getlobId');
    Route::any('/getprocessId', 'getprocessId')->name('getprocessId');
    Route::any('/sduploadfileImport', 'sduploadfileImport')->name('sduploadfileImport');
    Route::any('exportCIFailedOrders/{id}', 'exportCIFailedOrders')->name('exportCIFailedOrders');

    //Client
    Route::get('/addClient', 'addClient')->name('addClient');
    Route::post('/clientInsert', 'clientInsert')->name('clientInsert');
    // Route::post('/edit_client/{clientid?}', 'edit_client')->name('edit_client');
    Route::post('/getStateid', 'getState')->name('getStateid');
    Route::post('/getcountyid', 'getCounty')->name('getcountyid');
    Route::get('edit_client/{clientid?}', 'edit_client')->name('edit_client');
    Route::post('/clientupdate', 'clientupdate')->name('clientupdate');

    






    Route::any('/import', 'import')->name('import');
    Route::any('/export', 'export')->name('export');
});

Route::middleware('auth:web')->controller(OrderController::class)->group(function () {
    Route::get('orders', 'orders')->name('orders');
    Route::get('orders_status/{status_id?}', 'orders_status')->name('orders_status');
    Route::any('getOrderData', 'getOrderData')->name('getOrderData');
    Route::any('getStatusCount', 'getStatusCount')->name('getStatusCount');
    Route::get('orders/{order_id}', 'orderform')->name('orderform');
    Route::get('orderslist/{status_id?}', 'orderslist')->name('orderslist');
    Route::post('/assignOrder', 'assignOrder')->name('assignOrder');
    Route::post('assignment_update', 'assignment_update')->name('assignment_update');
    Route::any('/edit_order', 'edit_order')->name('edit_order');
    Route::any('/delete_order', 'delete_order')->name('delete_order');
    Route::post('update_order_status', 'update_order_status')->name('update_order_status');
    Route::post('redirectwithfilter', 'redirectwithfilter')->name('redirectwithfilter');

});

Route::middleware('auth:web')->controller(OrderFormController::class)->group(function () {
    Route::any('orderform/{order_id?}', 'index')->name('orderform');
    Route::post('orderform_submit', 'orderSubmit')->name('orderform_submit');
    Route::any('/coversheet-prep/{order_id?}', 'coversheet_prep')->name('coversheet-prep');

    Route::post('Product_dropdown', 'getProduct_dropdown')->name('Product_dropdown');
    Route::any('coversheet_submit', 'coversheet_submit')->name('coversheet_submit');
    Route::any('updateClickTime', 'updateClickTime')->name('updateClickTime');

    // accurate client
    Route::any('getaccurateClientId', 'getaccurateClientId')->name('getaccurateClientId');

});


Route::middleware('auth:web','role_or:Super Admin,PM/TL,AVP/VP,Business Head,SPOC')->controller(OrderCreationController::class)->group(function () {
    Route::any('single_order', 'single_order')->name('single_order');
    Route::any('OrderCreationsImport', 'OrderCreationsImport')->name('OrderCreationsImport');
    Route::any('InsertOrder', 'InsertOrder')->name('InsertOrder');
    Route::any('exportFailedOrders/{id}', 'exportFailedOrders')->name('exportFailedOrders');
    Route::any('/orderStatus/{order?}', 'orderStatus');
    Route::post('/edit_order', 'edit_order')->name('edit_order');
    Route::post('/updateOrder', 'updateOrder')->name('updateOrder');
    Route::post('/delete_order', 'delete_order')->name('delete_order');
    Route::any('/progressBar', 'progressBar')->name('progressBar');
    Route::post('unassign_user', 'unassign_user')->name('unassign_user');
    Route::post('unassign_qcer', 'unassign_qcer')->name('unassign_qcer');

    Route::any('/getStatus', 'getStatus')->name('getStatus');
    Route::any('/getlobid', 'getlobid')->name('getlobid');

    Route::any('/getlob', 'getlob')->name('getlob');//??
    Route::any('/getprocesstypeid', 'getprocesstypeid')->name('getprocesstypeid');
    Route::any('/getprocess_code', 'getprocess_code')->name('getprocess_code');

    Route::any('/getproduct', 'getproduct')->name('getproduct');//??

    Route::post('getCities', 'getCities')->name('getCities');


});


//reports


Route::middleware('auth:web','role_or:Super Admin,PM/TL,AVP/VP,Business Head,SPOC')->controller(ReportsController::class)->group(function () {
    Route::get('Reports', 'Reports')->name('Reports');
    Route::post('get_lob', 'get_lob')->name('get_lob');
    Route::post('get_process', 'get_process')->name('get_process');
    Route::post('get_product', 'get_product')->name('get_product');


    Route::post('userwise_count', 'userwise_count')->name('userwise_count');
    Route::post('orderWise', 'orderWise')->name('orderWise');

    Route::post('getGeoCounty', 'getGeoCounty')->name('getGeoCounty');
    Route::post('getGeoCities', 'getGeoCities')->name('getGeoCities');
    Route::any('get_timetaken', 'get_timetaken')->name('get_timetaken');
    Route::any('orderTimeTaken', 'orderTimeTaken')->name('orderTimeTaken');
    Route::any('attendance_report', 'attendance_report')->name('attendance_report');
    Route::any('production_report', 'production_report')->name('production_report');
    Route::any('exportProductionReport', 'exportProductionReport')->name('exportProductionReport');
    Route::any('orderInflow_data', 'orderInflow_data')->name('orderInflow_data');


});


Route::get('/test-email', function () {
    \Mail::raw('This is a test email', function ($message) {
        $message->to('shanmugam')
                ->subject('Test Email');
    });
    return 'Email sent!';
});