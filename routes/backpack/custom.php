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
    Route::group([
        'prefix' => 'vps',
        'namespace' => 'VPS',
        'middleware' => ['permission:vps']
    ], function () {
        CRUD::resource('server', 'ServerCrudController', ['middleware' => 'permission:vps-server']);
        Route::get('server/stats/{server_id}', 'ServerCrudController@stats');
        Route::get('server/docker/start', 'ServerCrudController@dockerStart');
        Route::get('server/docker/stop', 'ServerCrudController@dockerStop');
        Route::get('server/docker/config', 'ServerCrudController@getV2RayConfig');

        CRUD::resource('account', 'AccountCrudController', ['middleware' => 'permission:vps-account']);
    });

    Route::group([
        'prefix' => 'order',
        'namespace' => 'Order',
        'middleware' => ['permission:order']
    ], function () {
        CRUD::resource('distributor', 'DistributorCrudController', ['middleware' => 'permission:order-distributor']);
        CRUD::resource('customer', 'CustomerCrudController', ['middleware' => 'permission:order-customer']);
        CRUD::resource('order', 'OrderCrudController', ['middleware' => 'permission:order-order']);
    });
}); // this should be the absolute last line of this file