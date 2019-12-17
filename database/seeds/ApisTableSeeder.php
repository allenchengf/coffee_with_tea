<?php

use Illuminate\Database\Seeder;
use Hiero7\Models\Api;

class ApisTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Api $api)
    {
        $now = \Carbon\Carbon::now();

/*
 * =====================
 * 第 一 批 start
 * =====================
*/

/*
 * =======
 * Domain
 * =======
*/
        $api
        // Get Domain (pagination)
        // sidebar: Domains,Group,iRoueCDN
        ->updateOrCreate(
            ['id' => 1],
            ['method' => 'GET', 'path_regex' => 'domains', 'created_at' => $now],
        )
        // POST Create Domain
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 2],
            ['method' => 'POST', 'path_regex' => 'domains', 'created_at' => $now],
        )
        // POST Batch Create Domain & Cdn
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 3],
            ['method' => 'POST', 'path_regex' => 'domains\/batch', 'created_at' => $now],
        )
        // PUT Edit Domain
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 4],
            ['method' => 'PUT', 'path_regex' => 'domains\/[0-9]+', 'created_at' => $now],
        )
        // DELETE Domain
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 5],
            ['method' => 'DELETE', 'path_regex' => 'domains\/[0-9]+', 'created_at' => $now],
        )

/*
 * =======
 * CDN
 * =======
*/
        // GET Get All
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 6],
            ['method' => 'GET', 'path_regex' => 'domains\/[0-9]+\/cdn', 'created_at' => $now],
        )
        // POST Create
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 7],
            ['method' => 'POST', 'path_regex' => 'domains\/[0-9]+\/cdn', 'created_at' => $now],
        )
        // PATCH Udpate Default
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 8],
            ['method' => 'PATCH', 'path_regex' => 'domains\/[0-9]+\/cdn\/[0-9]+\/default', 'created_at' => $now],
        )
        // PATCH Udpate Cname
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 9],
            ['method' => 'PATCH', 'path_regex' => 'domains\/[0-9]+\/cdn\/[0-9]+\/cname', 'created_at' => $now],
        )
        // DELETE delete
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 10],
            ['method' => 'DELETE', 'path_regex' => 'domains\/[0-9]+\/cdn\/[0-9]+', 'created_at' => $now],
        )

/*
* =======
* IRouteCDN
* =======
*/
        // GET Get iRoute
        // sidebar: Domain, iRoueCDN
        ->updateOrCreate(
            ['id' => 11],
            ['method' => 'GET', 'path_regex' => 'domains\/[0-9]+\/routing-rules', 'created_at' => $now],
        )
        // PUT Edit Setting
        // sidebar: iRoueCDN
        ->updateOrCreate(
            ['id' => 12],
            ['method' => 'PUT', 'path_regex' => 'domains\/[0-9]+\/routing-rules\/[0-9]+', 'created_at' => $now],
        )
        // GET Get By Group/Domain
        // sidebar: iRoueCDN
        ->updateOrCreate(
            ['id' => 13],
            ['method' => 'GET', 'path_regex' => 'routing-rules\/lists', 'created_at' => $now],
        )
        // GET Get Group's iRoute
        // sidebar: Grouping, iRoueCDN
        ->updateOrCreate(
            ['id' => 14],
            ['method' => 'GET', 'path_regex' => 'groups\/[0-9]+\/routing-rules', 'created_at' => $now],
        )
        // PUT Edit Group's iRoute
        // sidebar: iRoueCDN
        ->updateOrCreate(
            ['id' => 15],
            ['method' => 'PUT', 'path_regex' => 'groups\/[0-9]+\/routing-rules\/[0-9]+', 'created_at' => $now],
        )
        // GET Get All iroute by Group/Domain
        // sidebar: iRoueCDN
        ->updateOrCreate(
            ['id' => 16],
            ['method' => 'GET', 'path_regex' => 'routing-rules\/all', 'created_at' => $now],
        )
        // GET Get All iroute by Group (pagination)
        // sidebar: iRoueCDN
        ->updateOrCreate(
            ['id' => 17],
            ['method' => 'GET', 'path_regex' => 'routing-rules\/groups', 'created_at' => $now],
        )
        // GET Get All iroute by Domain (pagination)
        // sidebar: iRoueCDN
        ->updateOrCreate(
            ['id' => 18],
            ['method' => 'GET', 'path_regex' => 'routing-rules\/domains', 'created_at' => $now],
        )
        // PATCH CDN Provider Scannable
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 19],
            ['method' => 'PATCH', 'path_regex' => 'cdn_providers\/[0-9]+\/scannable', 'created_at' => $now],
        )
        // GET Get Group's Domains
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 20],
            ['method' => 'GET', 'path_regex' => 'groups\/[0-9]+', 'created_at' => $now],
        )
        // POST Create Domain To Group
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 21],
            ['method' => 'POST', 'path_regex' => 'groups\/[0-9]+', 'created_at' => $now],
        )
        // POST Batch Group's Add Domain
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 22],
            ['method' => 'POST', 'path_regex' => 'groups\/[0-9]+\/batch', 'created_at' => $now],
        )
        // PUT Edit Group's Default Cdn
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 23],
            ['method' => 'PUT', 'path_regex' => 'groups\/[0-9]+\/defaultCdn', 'created_at' => $now],
        )
        // DELETE Delete Domain From Group
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 24],
            ['method' => 'DELETE', 'path_regex' => 'groups\/[0-9]+\/domain\/[0-9]+', 'created_at' => $now],
        )
        // GET Get Group
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 25],
            ['method' => 'GET', 'path_regex' => 'groups', 'created_at' => $now],
        )
        // POST Create Group
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 26],
            ['method' => 'POST', 'path_regex' => 'groups', 'created_at' => $now],
        )
        // PUT Edit Group
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 27],
            ['method' => 'PUT', 'path_regex' => 'groups\/[0-9]+', 'created_at' => $now],
        )
        // DELETE Delete Group
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 28],
            ['method' => 'DELETE', 'path_regex' => 'groups\/[0-9]+', 'created_at' => $now],
        )

/*
* =======
* Operation Logs
* =======
*/
        // GET Get Operation All Logs
        // sidebar: Logs
        ->updateOrCreate(
            ['id' => 29],
            ['method' => 'GET', 'path_regex' => 'operation_log', 'created_at' => $now],
        )
        // GET Get Operation Logs by Category
        // sidebar: Logs
        ->updateOrCreate(
            ['id' => 30],
            ['method' => 'GET', 'path_regex' => 'operation_log\/category\/[a-zA-Z]+', 'created_at' => $now],
        )
        // GET Get Operation Log Category List
        // sidebar: Logs
        ->updateOrCreate(
            ['id' => 31],
            ['method' => 'GET', 'path_regex' => 'operation_log\/category-list', 'created_at' => $now],
        )

/*
* =======
* Config
* =======
*/
        // GET get Config
        // sidebar: Tool > Config Backup
        ->updateOrCreate(
            ['id' => 32],
            ['method' => 'GET', 'path_regex' => 'config', 'created_at' => $now],
        )
        // POST import Config
        // sidebar: Tool > Config Backup
        ->updateOrCreate(
            ['id' => 33],
            ['method' => 'POST', 'path_regex' => 'config', 'created_at' => $now],
        )

/*
* =======
* Auto Scan
* =======
*/
        // PUT 一鍵切換 By Domain
        // sidebar: auto-scan
        ->updateOrCreate(
            ['id' => 34],
            ['method' => 'PUT', 'path_regex' => 'scan-platform\/domain\/[0-9]+', 'created_at' => $now],
        )
        // PUT 一鍵切換 By Domain Group
        // sidebar: auto-scan
        ->updateOrCreate(
            ['id' => 35],
            ['method' => 'PUT', 'path_regex' => 'scan-platform\/domain-group\/[0-9]+', 'created_at' => $now],
        )
        // PUT 一鍵切換
        // sidebar: auto-scan
        ->updateOrCreate(
            ['id' => 36],
            ['method' => 'PUT', 'path_regex' => 'scan-platform\/change-all', 'created_at' => $now],
        )

/*
* =======
* Process
* =======
*/
        // GET get Process Result
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 37],
            ['method' => 'GET', 'path_regex' => 'process\/result', 'created_at' => $now],
        )
        // GET get Process
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 38],
            ['method' => 'GET', 'path_regex' => 'process', 'created_at' => $now],
        )


/*
* =======
* Users
* =======
*/
        // GET Get Permission
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 39],
            ['method' => 'GET', 'path_regex' => 'permissions', 'created_at' => $now],
        )
        // GET Get Role Permission By Role ID
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 40],
            ['method' => 'GET', 'path_regex' => 'roles\/[0-9]+\/permissions', 'created_at' => $now],
        )
        // POST Upsert Role Permission By Role ID
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 41],
            ['method' => 'POST', 'path_regex' => 'roles\/[0-9]+\/permissions', 'created_at' => $now],
        )

/*
* =====================
* 第 一 批 end
* =====================
*/

/*
 * =====================
 * 第 二 批 start
 * =====================
*/
        // GET get Config Backup from S3
        // sidebar: Config
        ->updateOrCreate(
            ['id' => 42],
            ['method' => 'GET', 'path_regex' => 'config\/s3', 'created_at' => $now],
        )
        // GET Get Scanned Data (By Platform & CdnProvider)
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 43],
            ['method' => 'GET', 'path_regex' => 'scan-platform\/[0-9]+\/scanned-data', 'created_at' => $now],
        )
        // GET Get Scanned Data (All)
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 44],
            ['method' => 'GET', 'path_regex' => 'scan-platform\/scanned-data', 'created_at' => $now],
        )
        // GET Show Self Backup
        // sidebar: Config Backup
        ->updateOrCreate(
            ['id' => 45],
            ['method' => 'GET', 'path_regex' => 'backups\/self', 'created_at' => $now],
        )
        // POST Create Backup
        // sidebar: Config Backup
        ->updateOrCreate(
            ['id' => 46],
            ['method' => 'POST', 'path_regex' => 'backups', 'created_at' => $now],
        )
        // PUT Update Backup
        // sidebar: Config Backup
        ->updateOrCreate(
            ['id' => 47],
            ['method' => 'PUT', 'path_regex' => 'backups\/[0-9]+', 'created_at' => $now],
        )
        // GET Get Users
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 48],
            ['method' => 'GET', 'path_regex' => 'users', 'created_at' => $now],
        )
        // POST Create User
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 49],
            ['method' => 'POST', 'path_regex' => 'users', 'created_at' => $now],
        )
        // PUT User status
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 50],
            ['method' => 'PUT', 'path_regex' => 'users\/[0-9]+\/status', 'created_at' => $now],
        )
        // PUT Update Profile
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 51],
            ['method' => 'PUT', 'path_regex' => 'users\/[0-9]+\/profile', 'created_at' => $now],
        )
        // GET Get CDN Providers
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 52],
            ['method' => 'GET', 'path_regex' => 'cdn_providers', 'created_at' => $now],
        )
        // POST Create CDN Provider
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 53],
            ['method' => 'POST', 'path_regex' => 'cdn_providers', 'created_at' => $now],
        )
        // PATCH Edit CDN Provider
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 54],
            ['method' => 'PATCH', 'path_regex' => 'cdn_providers\/[0-9]+', 'created_at' => $now],
        )
        // PATCH 停止/回復 CDN
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 55],
            ['method' => 'PATCH', 'path_regex' => 'cdn_providers\/[0-9]+\/status', 'created_at' => $now],
        )
        // DELETE Delete CDN Provider
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 56],
            ['method' => 'DELETE', 'path_regex' => 'cdn_providers\/[0-9]+', 'created_at' => $now],
        )
        // GET Get Scan Platform
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 57],
            ['method' => 'GET', 'path_regex' => 'scan-platform', 'created_at' => $now],
        )
        // POST Create Scan Platform
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 58],
            ['method' => 'POST', 'path_regex' => 'scan-platform', 'created_at' => $now],
        )
        // PATCH Edit Scan Platform
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 59],
            ['method' => 'PATCH', 'path_regex' => 'scan-platform\/[0-9]+', 'created_at' => $now],
        )
        // DELETE Delete Scan Platform
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 60],
            ['method' => 'DELETE', 'path_regex' => 'scan-platform\/[0-9]+', 'created_at' => $now],
        )
        // POST Create Scanned Data
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 61],
            ['method' => 'POST', 'path_regex' => 'scan-platform\/[0-9]+\/scanned-data', 'created_at' => $now],
        )
        // GET Check Default CDN
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 62],
            ['method' => 'GET', 'path_regex' => 'cdn_providers', 'created_at' => $now],
        )
        // GET Scan CD 時間
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 63],
            ['method' => 'GET', 'path_regex' => 'scan-platform\/lock-time', 'created_at' => $now],
        )
        // GET Get Domain By Id
        // sidebar: Domains, Group, iRoueCDN
        ->updateOrCreate(
            ['id' => 64],
            ['method' => 'GET', 'path_regex' => 'domains\/[0-9]+', 'created_at' => $now],
        )

/*
* =====================
* 第 二 批 end
* =====================
*/
        ;
    }
}
