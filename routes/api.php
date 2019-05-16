<?php

Route::group(['middleware' => ['api'], 'namespace' => 'Api\v1', 'prefix' => 'v1'], function () {

    Route::group(['prefix' => 'domains'], function () {
        Route::get('', 'DomainController@getDomain')->name('domain.get');

        Route::middleware(['auth.user.module', 'domain.permission'])->group(function () {
            Route::post('', 'DomainController@create')->name('domain.create');

            Route::group(['prefix' => '/{domain}'], function () {
                Route::resource('/cdn', 'CdnController', ['except' => ['create', 'show', 'edit']]);
                
                //yuan
                Route::group(['prefix' => '/iRouteCDN'], function () {
                    Route::get('', 'LocationDnsSettingController@getAll')->name('iRoute.get');
                    Route::put('/{rid}', 'LocationDnsSettingController@editSetting')->name('iRoute.edit');
                });
            });

            Route::post('batch', 'BatchController@store');
        });

        Route::middleware(['domain.permission'])->group(function () {
            Route::put('{domain}', 'DomainController@editDomian')->name('domain.edit');
            Route::delete('{domain}', 'DomainController@destroy');
        });

    });

    Route::group(['middleware' => ['auth.user.module','internal.group'],  'prefix' => 'lines'], function () {
        Route::get('', 'lineController@index')->name('lines.index');
        Route::post('', 'lineController@create')->name('lines.create');
        Route::put('{line}', 'lineController@edit')->name('lines.edit');
        Route::delete('{line}', 'lineController@destroy')->name('lines.destroy');
    });

    Route::middleware(['auth.user.module'])->group(function(){
        Route::get('continents', 'ContinentController@index')->name('continents.index');
        Route::get('countries', 'CountryController@index')->name('countries.index');
        Route::get('networks', 'NetworkController@index')->name('networks.index');
    });
});
