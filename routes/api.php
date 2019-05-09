<?php

Route::group(['middleware' => ['api'], 'namespace' => 'Api\v1', 'prefix' => 'v1'], function () {

    Route::group(['prefix' => 'domain'], function () {
        Route::middleware(['internal.group'])->group(function () {
            Route::get('all', 'DomainController@getAllDomain');
        });

        Route::middleware(['auth.user.module'])->group(function () {
            Route::get('{ugid}', 'DomainController@getDomain');
            Route::post('', 'DomainController@create')->name('domain.create');
        });


    });

});
