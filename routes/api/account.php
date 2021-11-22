<?php

Route::group(['prefix' => 'account'], function () {
    Route::post('signup', 'CustomersController@signup'); 
    Route::get('email/{email}', 'CustomersController@email');
});


Route::group([
    'namespace' => 'Auth',
    'prefix' => 'account'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('requestpassword', 'PasswordResetController@create');

    Route::group([
    'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'AuthController@logout');
    });
});

Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'account'
    ], function() {
        Route::get('update', 'CustomersController@update');
        Route::put('update', 'CustomersController@updateCustomer');
        Route::get('financial', 'CustomersController@getFinancial');
    });