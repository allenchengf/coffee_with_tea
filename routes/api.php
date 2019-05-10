<?php

Route::group(['middleware' => ['api'], 'namespace' => 'Api\v1', 'prefix' => 'v1'], function () {

    Route::group(['middleware' => ['auth.user.module'], 'prefix' => 'domain'], function () {

        Route::post('', 'DomainController@create')->middleware('auth.user.module')->name('domain.create');


        Route::group(['prefix' => '/{domain}'], function () {

            Route::resource('/cdn', 'CdnController', ['except' => ['create', 'show', 'edit']]);
        });


    });

});
