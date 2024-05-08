<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderCreationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\OrderFormController;
use Illuminate\Support\Facades\Route;

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
    Route::post('dashboard_datewise_count', 'dashboard_datewise_count')->name('dashboard_datewise_count');
    Route::post('dashboard_userwise_count', 'dashboard_userwise_count')->name('dashboard_userwise_count');
    Route::post('revenue_detail', 'revenue_detail')->name('revenue_detail');
    Route::post('revenue_detail_client', 'revenue_detail_client')->name('revenue_detail_client');
    Route::post('order_detail', 'order_detail')->name('order_detail');
    Route::post('getTotalData', 'getTotalData')->name('getTotalData');

    Route::post('getTotalDataFte', 'getTotalDataFte')->name('getTotalDataFte');

    Route::post('revenue_detail_process_fte', 'revenue_detail_process_fte')->name('revenue_detail_process_fte');
    Route::post('revenue_detail_processDetail_fte', 'revenue_detail_processDetail_fte')->name('revenue_detail_processDetail_fte');
    Route::post('revenue_detail_process_total_fte', 'revenue_detail_process_total_fte')->name('revenue_detail_process_total_fte');

    Route::post('dashboard_dropdown', 'dashboard_dropdown')->name('dashboard_dropdown');
    Route::post('revenue_detail_client_fte', 'revenue_detail_client_fte')->name('revenue_detail_client_fte');


});

Route::middleware(['auth:web', 'role_or:Super Admin,PM/TL,AVP/VP,Business Head,SPOC'])->controller(SettingController::class)->group(function () {
    Route::get('settings', 'setting')->name('settings');
    Route::get('settings/users', 'setting')->name('users');
    // Users
    Route::post('/usersInsert', 'addUsers')->name('usersInsert');
    Route::post('edit_user', 'edit_user')->name('edit_user');
    Route::post('/updateUsers', 'updateUsers')->name('updateUsers');
    Route::any('/userStatus/{userid?}', 'userStatus');
    Route::post('/mappingData', 'mappingData')->name('mappingData');
    Route::any('/addMapping', 'addMapping')->name('addMapping');
    Route::post('/removeMapping', 'removeMapping')->name('removeMapping');
    Route::post('/show_user', 'showUser')->name('show_user');
    Route::post('/getUserList', 'getUserList')->name('getUserList');

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

    Route::any('/getStatus', 'getStatus')->name('getStatus');

});
