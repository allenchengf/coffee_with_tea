<?php

Route::group(['middleware' => ['api'], 'namespace' => 'Api\v1', 'prefix' => 'v1'], function () {

    Route::group(['prefix' => 'domains'], function () {
        Route::middleware(['internal.group'])->group(function () {
            Route::get('all', 'DomainController@getAllDomain');
        });

        Route::middleware(['auth.user.module'])->group(function () {
            Route::get('{user_group_id}', 'DomainController@getDomain');
            Route::post('', 'DomainController@create')->name('domain.create');
            
            Route::group(['prefix' => '/{domain}'], function () {
                Route::resource('/cdn', 'CdnController', ['except' => ['create', 'show', 'edit']]);
            });

            Route::post('batch', 'BatchController@store');
        });

        Route::put('{domain}', 'DomainController@editDomian')->name('domain.edit');
        Route::delete('{domain}', 'DomainController@destroy');

    });
});