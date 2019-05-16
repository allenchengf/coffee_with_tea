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

    Route::group(['middleware' => ['auth.user.module','internal.group'],  'prefix' => 'lines'], function () {
        Route::get('', 'LineController@index')->name('lines.index');
        Route::post('', 'LineController@create')->name('lines.create');
        Route::put('{line}', 'LineController@edit')->name('lines.edit');
        Route::delete('{line}', 'LineController@destroy')->name('lines.destroy');
    });

    Route::middleware(['auth.user.module'])->group(function(){
        Route::get('continents', 'ContinentController@index')->name('continents.index');
        Route::get('countries', 'CountryController@index')->name('countries.index');
        Route::get('networks/{network}', 'NetworkController@index')->name('networks.index');
    });

    Route::group(['middleware' => ['auth.user.module','internal.group'],  'prefix' => 'schemes'], function () {
        Route::get('', 'SchemeController@index')->name('schemes.index');
        Route::post('', 'SchemeController@create')->name('schemes.create');
        Route::put('{scheme}', 'SchemeController@edit')->name('schemes.edit');
        Route::delete('{scheme}', 'SchemeController@destroy')->name('schemes.destroy');
    });
});
