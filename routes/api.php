<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['prefix' => 'v1'], function () {

    require "api/account.php";

    require "api/categories.php";

    require "api/badges.php";

    require "api/address.php";
    
    require "api/plans.php";

    Route::get('coupons/{code}', 'CouponsController@find');

    Route::get('settings', 'SettingsController@getAll');

    Route::get('settings/{tag}', 'SettingsController@getTag');

    Route::get('banners', 'BannersController@get');

    Route::post('banners', 'BannersController@upload');

});