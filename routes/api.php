<?php

Route::group(['middleware' => ['api'], 'namespace' => 'Api\v1', 'prefix' => 'v1'], function () {

    Route::group(['prefix' => 'domains'], function () {
        Route::get('', 'DomainController@getDomain')->name('domain.get');

        Route::middleware(['auth.user.module', 'domain.permission'])->group(function () {
            Route::post('', 'DomainController@create')->name('domain.create');

            Route::group(['prefix' => '/{domain}'], function () {
                Route::resource('/cdn', 'CdnController', ['except' => ['create', 'show', 'edit']]);
            });

            Route::post('batch', 'BatchController@store');
        });

        Route::middleware(['domain.permission'])->group(function () {
            Route::put('{domain}', 'DomainController@editDomian')->name('domain.edit');
            Route::delete('{domain}', 'DomainController@destroy');
        });

    });
});
