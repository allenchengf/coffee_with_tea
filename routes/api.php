<?php

Route::group(['middleware' => ['api'], 'namespace' => 'Api\v1', 'prefix' => 'v1'], function () {

    Route::group(['prefix' => 'domains'], function () {
        Route::get('', 'DomainController@getDomain')->name('domain.get');
        
        Route::group(['prefix' => '/iRouteCDN'], function () {
            Route::get('', 'LocationDnsSettingController@getAll')->name('iRoute.get');
            Route::put('/{recordId}', 'LocationDnsSettingController@editSetting')->name('iRoute.edit');
        });

        Route::middleware(['auth.user.module'])->group(function () {
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