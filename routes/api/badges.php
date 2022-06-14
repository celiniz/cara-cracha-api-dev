<?php

//Search for badges
Route::get('badges', 'BadgesController@search');

//Search for a specific badge
Route::get('badges/{id}', 'BadgesController@find')->where('id', '[0-9]+');

//Register badge click on phone
Route::get('badges/click/phone/{id}', 'BadgesController@badgePhone');

//Register badge click on whats
Route::get('badges/click/whats/{id}', 'BadgesController@badgeWhats');

//Create a subscriptions to a badge
Route::post('badges/subscribe', 'BadgesController@subscribe');

//Create a badge for a new customer
Route::post('badges/create', 'BadgesController@create');

//Create a review to badge
Route::get('reviews/{id}', 'BadgesController@getReviews');

//Upload pictures by file
Route::post('badges/photos', 'BadgesController@uploadPhotosByFile');

//Upload picture by file on website
Route::post('badges/photoSite', 'BadgesController@uploadEditPhotoSite');

//Delete picture by file on website
Route::post('badges/deletePhotoSite', 'BadgesController@deleteEditPhotoSite');

//Get max photos service
Route::get('badges/maxPhotosService', 'BadgesController@getMaxPhotosService');

Route::group([
    'middleware' => 'auth:api'
], function () {

    //Get my badges
    Route::get('account/badges', 'BadgesController@myBadges');
    
    //Create a badge to an existing account
    Route::post('account/badges/create', 'BadgesController@create');    
    
    //Update a badge
    Route::put('badges/update/{id}', 'BadgesController@updateBadge');
    
    //Create a review to badge
    Route::post('review/{id}', 'BadgesController@createReview');

    //Change badge plan
    Route::post('account/badges/change', 'BadgesController@changePlanProcess');
    Route::put('account/badges', 'BadgesController@cancelBadge');
    Route::delete('account/badges', 'BadgesController@deleteBadge');
});