<?php

Route::group(['prefix' => 'categories'], function () {
    
    Route::get('featured', 'CategoriesController@featured');
    
    Route::get('/', 'CategoriesController@categories');

    Route::post('/notfound', 'CategoriesController@notfound');
});