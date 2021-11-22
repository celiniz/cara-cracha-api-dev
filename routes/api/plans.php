<?php

Route::group([
    'middleware' => 'auth:api'
], function () {
    Route::get('plans/teste', 'PlansController@getall');
});
Route::get('plans', 'PlansController@getall');