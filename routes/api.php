<?php
Route::group([
    'middleware' => ['api', 'auth.jwt', 'check.role.permission', 'check.config'],
    'namespace'  => 'Api\v1',
    'prefix'     => 'v1',
], function () {

    Route::group(['prefix' => 'domains'], function () {

        // Leo Edit
        Route::group(['middleware' => ['internal.group', 'check.dnspod']], function () {
            Route::get('check', 'DnsPodRecordSyncController@index');
            Route::get('{domain}/check', 'DnsPodRecordSyncController@getDomain');
            Route::get('check-diff', 'DnsPodRecordSyncController@checkDataDiff');

            // 用於同步 DNS Pod 與 DB Data 的 API
            // 已 DB 資料為準
            Route::post('sync', 'DnsPodRecordSyncController@syncDnsData');
        });

        Route::get('', 'DomainController@getDomain')->name('domain.index');
        Route::get('sql', 'DomainController@getDomainSqlJoin')->name('domain.indexSql');
        Route::get('count', 'DomainController@countDomain')->name('domain.count');

        Route::middleware(['domain.permission'])->group(function () {
            Route::post('', 'DomainController@create')->name('domain.create');

            Route::group(['prefix' => '/{domain}'], function () {
                Route::post('check-sync', 'DnsPodRecordSyncController@syncDnsDataByDomain');

                Route::get('/cdn', 'CdnController@index')->name('cdn.index');
                Route::resource('/cdn', 'CdnController', ['only' => ['store', 'destroy']])->middleware('check.dnspod');
                Route::patch('cdn/{cdn}/default',
                    'CdnController@updateDefault')->name('cdn.default')->middleware('check.dnspod');
                Route::patch('cdn/{cdn}/cname',
                    'CdnController@updateCname')->name('cdn.cname')->middleware('check.dnspod');

                Route::group(['prefix' => '/routing-rules'], function () {
                    Route::get('', 'LocationDnsSettingController@indexByDomain')->name('iRoute.indexByDomain');
                    Route::put('/{locationNetwork}',
                        'LocationDnsSettingController@editSetting')->name('iRoute.edit')->middleware('check.dnspod');
                });
            });

            Route::post('batch', 'BatchController@store')->name('domains.batch')->middleware([
                'check.dnspod',
                'auth.user.module',
            ]);
            Route::post('oldBatch', 'BatchController@oldStore')->name('domains.batch')->middleware([
                'check.dnspod',
                'auth.user.module',
            ]);
        });

        Route::middleware(['domain.permission'])->group(function () {
            Route::get('{domain}', 'DomainController@getDomainById');
            Route::put('{domain}', 'DomainController@editDomain')->name('domain.edit');
            Route::delete('{domain}', 'DomainController@destroy')->middleware('check.dnspod');
        });
    });

    Route::group(['middleware' => ['internal.group']], function () {

        Route::group(['prefix' => 'domain-pin'], function () {
            Route::get('', 'DomainPinController@index');
            Route::get('{domainPin}', 'DomainPinController@show');
            Route::post('', 'DomainPinController@store');
            Route::delete('{domainPin}', 'DomainPinController@destroy');
        });

        Route::group(['prefix' => 'lines'], function () {
            Route::get('', 'LineController@index')->name('lines.index');
            Route::post('', 'LineController@create')->name('lines.create');
            Route::put('{line}', 'LineController@edit')->name('lines.edit');
            Route::patch('{line}/status',
                'LineController@changeStatus')->name('lines.status')->middleware('check.dnspod');
            Route::delete('{line}', 'LineController@destroy')->name('lines.destroy')->middleware('check.dnspod');
        });

        Route::group(['prefix' => 'schemes'], function () {
            Route::get('', 'SchemeController@index')->name('schemes.index');
            Route::post('', 'SchemeController@create')->name('schemes.create');
            Route::put('{scheme}', 'SchemeController@edit')->name('schemes.edit');
            Route::delete('{scheme}', 'SchemeController@destroy')->name('schemes.destroy');
        });

        Route::resource('networks', 'NetworkController', ['only' => ['store']]);

        Route::get('schemes/{scheme_id}/networks', 'NetworkController@index')->name('networks.index');
    });

    Route::get('networks', 'NetworkController@getList')->name('networks.getList');
    Route::get('continents', 'ContinentController@index')->name('continents.index');
    Route::get('countries', 'CountryController@index')->name('countries.index');

    Route::group(['prefix' => 'cdn_providers'], function () {
        Route::get('', 'CdnProviderController@index')->name('cdn_providers.index');
        Route::post('', 'CdnProviderController@store')->name('cdn_providers.store');
        Route::patch('{cdn_provider}',
            'CdnProviderController@update')->name('cdn_providers.update')->middleware('check.dnspod');
        Route::patch('{cdn_provider}/status',
            'CdnProviderController@changeStatus')->name('cdn_providers.status')->middleware('check.dnspod');
        Route::patch('{cdn_provider}/scannable',
            'CdnProviderController@changeScannable')->name('cdn_providers.scannable');
        Route::get('{cdn_provider}/check', 'CdnProviderController@checkDefault')->name('cdn_providers.check');
        Route::delete('{cdn_provider}',
            'CdnProviderController@destroy')->name('cdn_providers.destroy')->middleware('check.dnspod');
        Route::get('detailed-info', 'CdnProviderController@detailedInfo')->name('cdn_providers.detailedInfo');
    });

    Route::group(['prefix' => 'groups'], function () {
        Route::get('', 'DomainGroupController@index')->name('groups.index');
        Route::get('{domainGroup}/routing-rules',
            'DomainGroupController@indexGroupIroute')->name('groups.indexGroupIroute');
        Route::get('{domainGroup}', 'DomainGroupController@indexByDomainGroupId')->name('groups.indexByDomainGroupId');
        Route::post('', 'DomainGroupController@create')->name('groups.create');
        Route::post('{domainGroup}',
            'DomainGroupController@createDomainToGroup')->name('groups.createDomainToGroup')->middleware('check.dnspod');
        Route::put('{domainGroup}', 'DomainGroupController@edit')->name('groups.edit');
        Route::put('{domainGroup}/defaultCdn',
            'DomainGroupController@changeDefaultCdn')->name('groups.changeDefaultCdn');
        Route::put('{domainGroup}/routing-rules/{locationNetwork}',
            'DomainGroupController@updateRouteCdn')->name('groups.updateRouteCdn')->middleware('check.dnspod');
        Route::delete('{domainGroup}', 'DomainGroupController@destroy')->name('groups.destroy');
        Route::delete('{domainGroup}/domain/{domain}',
            'DomainGroupController@destroyByDomainId')->name('groups.destroyByDomainId');
        Route::post('{domainGroup}/batch', 'BatchController@storeDomainToGroup')->name('groups.batch')->middleware([
            'check.dnspod',
            'auth.user.module',
        ]);
    });

    Route::group(['prefix' => 'routing-rules'], function () {
        Route::get('/lists', 'LocationDnsSettingController@indexByGroup')->name('iRoute.indexByGroup');
        Route::get('/all', 'LocationDnsSettingController@indexAll')->name('iRoute.indexAll');
        Route::get('groups', 'LocationDnsSettingController@indexGroups')->name('iRoute.indexGroups');
        Route::get('domains', 'LocationDnsSettingController@indexDomains')->name('iRoute.indexDomains');
    });

    Route::group(['prefix' => 'config'], function () {
        Route::get('', 'ConfigController@index')->name('config.index');
        Route::post('', 'ConfigController@import')->name('config.import')->middleware('check.dnspod');

        Route::get('s3', 'ConfigController@indexBackupFromS3')->name('config.indexBackup');
        Route::get('s3/{key}', 'ConfigController@showBackupFromS3')->name('config.showBackup');
        Route::post('s3', 'ConfigController@storeBackupByUgid')->name('config.storeBackupToS3');
        Route::put('s3/{key}', 'ConfigController@restoreBackupFromS3')->name('config.restoreBackup');
    });

    Route::group(['prefix' => 'backups'], function () {
        Route::get('self', 'BackupController@show')->name('backups.show');
        Route::put('', 'BackupController@upsert')->name('backups.upsert');
    });

    Route::group(['prefix' => 'operation_log'], function () {
        Route::get('', 'OperationLogController@index')->name('operation_log.index');
        Route::get('category/{category}', 'OperationLogController@show')->name('operation_log.show');
        Route::get('category-list', 'OperationLogController@categoryList')->name('operation_log.category.list');
    });

    Route::group(['prefix' => 'scan-platform'], function () {
        Route::get('lock-time', 'ScanProviderController@checkLockTime');

        Route::get('', 'ScanPlatformController@index')->name('scanPlatform.index');
        Route::post('', 'ScanPlatformController@create')->name('scanPlatform.create');
        Route::patch('{scanPlatform}', 'ScanPlatformController@edit')->name('scanPlatform.edit');
        Route::delete('{scanPlatform}', 'ScanPlatformController@destroy')->name('scanPlatform.destroy');

        Route::post('{scanPlatform}/scanned-data', 'ScanProviderController@creatScannedData')->name('scan.create');
        Route::get('{scanPlatform}/scanned-data',
            'ScanProviderController@indexScannedDataByPlatform')->name('scan.show');
        Route::get('scanned-data', 'ScanProviderController@indexScannedData')->name('scan.index');

        Route::put('change-all', 'ScanProviderController@changeRegion')->middleware('check.dnspod');

        Route::middleware(['domain.permission', 'check.dnspod'])->group(function () {
            Route::put('domain/{domain}', 'ScanProviderController@changeDomainRegion');
            Route::put('domain-group/{domainGroup}', 'ScanProviderController@changeDomainGroupRegion');
        });
    });

    Route::group(['prefix' => 'process'], function () {
        Route::get('', 'ProcessController@index')->name('process.index');
        Route::get('result', 'ProcessController@getBatchResult')->name('process.getBatchResult');
    });

    Route::group(['prefix' => 'roles'], function () {
        Route::get('self/permissions',
            'RolePermissionMappingController@indexSelf')->name('role_permission_mapping.indexSelf');
        Route::get('{roleId}/permissions',
            'RolePermissionMappingController@indexByRoleId')->name('role_permission_mapping.indexByRoleId');
        Route::post('{roleId}/permissions',
            'RolePermissionMappingController@upsert')->name('role_permission_mapping.upsert');
        Route::delete('{roleId}/permissions',
            'RolePermissionMappingController@destroy')->name('role_permission_mapping.destroy');
    });

    Route::group(['prefix' => 'permissions'], function () {
        Route::get('', 'PermissionController@index')->name('permissions.index');
    });

});

Route::group(['middleware' => ['api'], 'namespace' => 'Api\v1', 'prefix' => 'v1'], function () {
    Route::get('log/cdn-change', 'OperationLogController@getForPortalLog')->name('operation_log.forPortal');
});
