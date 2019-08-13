<?php
ini_set('max_execution_time', 600);
ini_set('memory_limit', '1024M');

Route::group(['middleware' => ['api','check.config'], 'namespace' => 'Api\v1', 'prefix' => 'v1'], function () {
    Route::group(['prefix' => 'domains'], function () {

        Route::get('check', 'DnsPodRecordSyncController@index');
        Route::get('{domain}/check', 'DnsPodRecordSyncController@getDomain');
        Route::get('check-diff', 'DnsPodRecordSyncController@checkDataDiff');
        Route::post('sync', 'DnsPodRecordSyncController@syncDnsData');

        Route::get('', 'DomainController@getDomain')->name('domain.index');
        Route::middleware(['auth.user.module', 'domain.permission'])->group(function () {
            Route::post('', 'DomainController@create')->name('domain.create');

            Route::group(['prefix' => '/{domain}'], function () {
                Route::resource('/cdn', 'CdnController', ['only' => ['index', 'store', 'destroy']]);
                Route::patch('cdn/{cdn}/default', 'CdnController@updateDefault')->name('cdn.default');
                Route::patch('cdn/{cdn}/cname', 'CdnController@updateCname')->name('cdn.cname');

                //yuan
                Route::group(['prefix' => '/routing-rules'], function () {
                    Route::get('', 'LocationDnsSettingController@indexByDomain')->name('iRoute.indexByDomain');
                    Route::middleware(['admin.check'])->group(function() {
                        Route::put('/{locationNetworkId}', 'LocationDnsSettingController@editSetting')->name('iRoute.edit');
                    });
                });
            });

            Route::post('batch', 'BatchController@store')->name('domains.batch');
        });

        Route::middleware(['domain.permission'])->group(function () {
            Route::get('{domain}', 'DomainController@getDomainById');
            Route::put('{domain}', 'DomainController@editDomain')->name('domain.edit');
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
        Route::get('schemes/{scheme_id}/networks', 'NetworkController@index')->name('networks.index');
    });

    Route::group(['middleware' => ['auth.user.module','internal.group'],  'prefix' => 'schemes'], function () {
        Route::get('', 'SchemeController@index')->name('schemes.index');
        Route::post('', 'SchemeController@create')->name('schemes.create');
        Route::put('{scheme}', 'SchemeController@edit')->name('schemes.edit');
        Route::delete('{scheme}', 'SchemeController@destroy')->name('schemes.destroy');
    });

    Route::group(['middleware' => ['auth.user.module'],  'prefix' => 'cdn_providers'], function () {
        Route::get('', 'CdnProviderController@index')->name('cdn_providers.index');
        Route::post('', 'CdnProviderController@store')->name('cdn_providers.store');
        Route::patch('{cdn_provider}', 'CdnProviderController@update')->name('cdn_providers.update');
        Route::patch('{cdn_provider}/status', 'CdnProviderController@changeStatus')->name('cdn_providers.status');
        Route::get('{cdn_provider}/check', 'CdnProviderController@checkDefault')->name('cdn_providers.check');
        Route::delete('{cdn_provider}', 'CdnProviderController@destroy')->name('cdn_providers.destroy');
    });

    Route::group(['middleware' => ['auth.user.module'], 'prefix' => 'groups'], function(){
        Route::get('', 'DomainGroupController@index')->name('groups.index');
        Route::get('{domainGroup}/routing-rules', 'DomainGroupController@indexGroupIroute')->name('groups.indexGroupIroute');
        Route::get('{domainGroup}', 'DomainGroupController@indexByDomainGroupId')->name('groups.indexByDomainGroupId');
        Route::post('', 'DomainGroupController@create')->name('groups.create');
        Route::post('{domainGroup}', 'DomainGroupController@createDomainToGroup')->name('groups.createDomainToGroup');
        Route::put('{domainGroup}', 'DomainGroupController@edit')->name('groups.edit');
        Route::put('{domainGroup}/defaultCdn', 'DomainGroupController@changeDefaultCdn')->name('groups.changeDefaultCdn');
        Route::put('{domainGroup}/routing-rules/{locationNetwork}', 'DomainGroupController@updateRouteCdn')->name('groups.updateRouteCdn');
        Route::delete('{domainGroup}', 'DomainGroupController@destroy')->name('groups.destroy');
        Route::delete('{domainGroup}/domain/{domain}', 'DomainGroupController@destroyByDomainId')->name('groups.destroyByDomainId');

        Route::post('{domainGroup}/batch', 'BatchController@storeDomainToGroup')->name('groups.batch');

    });


    Route::group(['prefix' => 'routing-rules'], function () {
        Route::get('/lists', 'LocationDnsSettingController@indexByGroup')->name('iRoute.indexByGroup');
        Route::get('/all', 'LocationDnsSettingController@indexAll')->name('iRoute.indexAll');
    });

    Route::group(['middleware' => ['check.config'],'prefix' => 'config'], function () {
        Route::get('', 'ConfigController@index')->name('config.index');
        Route::post('', 'ConfigController@import')->name('config.indexByGroup');
    });

    Route::group(['middleware' => ['auth.user.module'], 'prefix' => 'operation_log'], function () {
        Route::get('', 'OperationLogController@index')->name('operation_log.index');
    });


    Route::group(['prefix' => 'scan-provider'],function () {
        Route::get('', 'ScanProviderController@index');
        Route::put('routing-rules', 'ScanProviderController@changeCdnProvider')->name('scan.chage');
        Route::patch('routing-rules', 'ScanProviderController@changeToCdnProvider')->name('scan.chage2');
    });
});
