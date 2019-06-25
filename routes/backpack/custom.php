<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', config('backpack.base.middleware_key', 'admin')],
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes

    Route::get('dashboard', 'DashboardController@index')->name('backpack.dashboard');

    Route::group([
        'prefix' => 'vps',
        'namespace' => 'VPS',
        'middleware' => ['permission:vps']
    ], function () {
        Route::get('server/order-list', 'ServerCrudController@serverOrderList')->middleware(['permission:vps-server-list']);

        CRUD::resource('account', 'AccountCrudController', ['middleware' => 'permission:vps-accounts']);
        CRUD::resource('server', 'ServerCrudController', ['middleware' => 'permission:vps-servers']);
        Route::get('server/stats/{server_id}', 'ServerCrudController@stats')->middleware(['permission:vps-servers']);
        Route::get('server/docker/start', 'ServerCrudController@dockerStart')->middleware(['permission:vps-servers']);
        Route::get('server/docker/stop', 'ServerCrudController@dockerStop')->middleware(['permission:vps-servers']);
        Route::get('server/docker/redo', 'ServerCrudController@dockerRedo')->middleware(['permission:vps-servers']);
        Route::get('server/docker/config', 'ServerCrudController@getV2RayConfig')->middleware(['permission:vps-servers']);

        CRUD::resource('serverlog', 'ServerLogCrudController', ['middleware' => 'permission:vps-server-logs']);
    });

    Route::group([
        'prefix' => 'order',
        'namespace' => 'Order',
        'middleware' => ['permission:order']
    ], function () {
        CRUD::resource('distributor', 'DistributorCrudController', ['middleware' => 'permission:order-distributors']);
        CRUD::resource('customer', 'CustomerCrudController', ['middleware' => 'permission:order-customers']);
        CRUD::resource('order', 'OrderCrudController', ['middleware' => 'permission:order-orders']);
    });

    Route::group([
        'prefix' => 'finance',
        'namespace' => 'Finance',
        'middleware' => ['permission:finance']
    ], function () {
        CRUD::resource('cost', 'CostCrudController', ['middleware' => 'permission:finance-cost']);
        CRUD::resource('settlement', 'SettlementCrudController', ['middleware' => 'permission:finance-settle']);
        Route::get('settle/preview', 'SettlementCrudController@settlePreview')->middleware(['permission:finance-settle']);
        Route::post('settle', 'SettlementCrudController@settleIncomes')->middleware(['permission:finance-settle']);
    });
}); // this should be the absolute last line of this file