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
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'domains', 'created_at' => $now],
            ['id' => 1]
        )
        // POST Create Domain
        // sidebar: Domains
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'domains', 'created_at' => $now],
            ['id' => 2]
        )
        // POST Batch Create Domain & Cdn
        // sidebar: Domains
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'domains\/batch', 'created_at' => $now],
            ['id' => 3]
        )
        // PUT Edit Domain
        // sidebar: Domains
        ->updateOrInsert(
            ['method' => 'PUT', 'path_regex' => 'domains\/[0-9]+', 'created_at' => $now],
            ['id' => 4]
        )
        // DELETE Domain
        // sidebar: Domains
        ->updateOrInsert(
            ['method' => 'DELETE', 'path_regex' => 'domains\/[0-9]+', 'created_at' => $now],
            ['id' => 5]
        )

/*
 * =======
 * CDN
 * =======
*/
        // GET Get All
        // sidebar: Domains
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'domains\/[0-9]+\/cdn', 'created_at' => $now],
            ['id' => 6]
        )
        // POST Create
        // sidebar: Domains
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'domains\/[0-9]+\/cdn', 'created_at' => $now],
            ['id' => 7]
        )
        // PATCH Udpate Default
        // sidebar: Domains
        ->updateOrInsert(
            ['method' => 'PATCH', 'path_regex' => 'domains\/[0-9]+\/cdn\/[0-9]+\/default', 'created_at' => $now],
            ['id' => 8]
        )
        // PATCH Udpate Cname
        // sidebar: Domains
        ->updateOrInsert(
            ['method' => 'PATCH', 'path_regex' => 'domains\/[0-9]+\/cdn\/[0-9]+\/cname', 'created_at' => $now],
            ['id' => 9]
        )
        // DELETE delete
        // sidebar: Domains
        ->updateOrInsert(
            ['method' => 'DELETE', 'path_regex' => 'domains\/[0-9]+\/cdn\/[0-9]+', 'created_at' => $now],
            ['id' => 10]
        )

/*
* =======
* IRouteCDN
* =======
*/
        // GET Get iRoute
        // sidebar: Domain, iRoueCDN
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'domains\/[0-9]+\/routing-rules', 'created_at' => $now],
            ['id' => 11]
        )
        // PUT Edit Setting
        // sidebar: iRoueCDN
        ->updateOrInsert(
            ['method' => 'PUT', 'path_regex' => 'domains\/[0-9]+\/routing-rules\/[0-9]+', 'created_at' => $now],
            ['id' => 12]
        )
        // GET Get By Group/Domain
        // sidebar: iRoueCDN
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'routing-rules\/lists', 'created_at' => $now],
            ['id' => 13]
        )
        // GET Get Group's iRoute
        // sidebar: Grouping, iRoueCDN
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'groups\/[0-9]+\/routing-rules', 'created_at' => $now],
            ['id' => 14]
        )
        // PUT Edit Group's iRoute
        // sidebar: iRoueCDN
        ->updateOrInsert(
            ['method' => 'PUT', 'path_regex' => 'groups\/[0-9]+\/routing-rules\/[0-9]+', 'created_at' => $now],
            ['id' => 15]
        )
        // GET Get All iroute by Group/Domain
        // sidebar: iRoueCDN
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'routing-rules\/all', 'created_at' => $now],
            ['id' => 16]
        )
        // GET Get All iroute by Group (pagination)
        // sidebar: iRoueCDN
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'routing-rules\/groups', 'created_at' => $now],
            ['id' => 17]
        )
        // GET Get All iroute by Domain (pagination)
        // sidebar: iRoueCDN
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'routing-rules\/domains', 'created_at' => $now],
            ['id' => 18]
        )
        // PATCH CDN Provider Scannable
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['method' => 'PATCH', 'path_regex' => 'cdn_providers\/[0-9]+\/scannable', 'created_at' => $now],
            ['id' => 19]
        )
        // GET Get Group's Domains
        // sidebar: Grouping
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'groups\/[0-9]+', 'created_at' => $now],
            ['id' => 20]
        )
        // POST Create Domain To Group
        // sidebar: Grouping
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'groups\/[0-9]+', 'created_at' => $now],
            ['id' => 21]
        )
        // POST Batch Group's Add Domain
        // sidebar: Grouping
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'groups\/[0-9]+\/batch', 'created_at' => $now],
            ['id' => 22]
        )
        // PUT Edit Group's Default Cdn
        // sidebar: Grouping
        ->updateOrInsert(
            ['method' => 'PUT', 'path_regex' => 'groups\/[0-9]+\/defaultCdn', 'created_at' => $now],
            ['id' => 23]
        )
        // DELETE Delete Domain From Group
        // sidebar: Grouping
        ->updateOrInsert(
            ['method' => 'DELETE', 'path_regex' => 'groups\/[0-9]+\/domain\/[0-9]+', 'created_at' => $now],
            ['id' => 24]
        )
        // GET Get Group
        // sidebar: Grouping
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'groups', 'created_at' => $now],
            ['id' => 25]
        )
        // POST Create Group
        // sidebar: Grouping
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'groups', 'created_at' => $now],
            ['id' => 26]
        )
        // PUT Edit Group
        // sidebar: Grouping
        ->updateOrInsert(
            ['method' => 'PUT', 'path_regex' => 'groups\/[0-9]+', 'created_at' => $now],
            ['id' => 27]
        )
        // DELETE Delete Group
        // sidebar: Grouping
        ->updateOrInsert(
            ['method' => 'DELETE', 'path_regex' => 'groups\/[0-9]+', 'created_at' => $now],
            ['id' => 28]
        )

/*
* =======
* Operation Logs
* =======
*/
        // GET Get Operation All Logs
        // sidebar: Logs
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'operation_log', 'created_at' => $now],
            ['id' => 29]
        )
        // GET Get Operation Logs by Category
        // sidebar: Logs
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'operation_log\/category\/[a-zA-Z]+', 'created_at' => $now],
            ['id' => 30]
        )
        // GET Get Operation Log Category List
        // sidebar: Logs
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'operation_log\/category-list', 'created_at' => $now],
            ['id' => 31]
        )

/*
* =======
* Config
* =======
*/
        // GET get Config
        // sidebar: Tool > Config Backup
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'config', 'created_at' => $now],
            ['id' => 32]
        )
        // POST import Config
        // sidebar: Tool > Config Backup
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'config', 'created_at' => $now],
            ['id' => 33]
        )

/*
* =======
* Auto Scan
* =======
*/
        // PUT 一鍵切換 By Domain
        // sidebar: auto-scan
        ->updateOrInsert(
            ['method' => 'PUT', 'path_regex' => 'scan-platform\/domain\/[0-9]+', 'created_at' => $now],
            ['id' => 34]
        )
        // PUT 一鍵切換 By Domain Group
        // sidebar: auto-scan
        ->updateOrInsert(
            ['method' => 'PUT', 'path_regex' => 'scan-platform\/domain-group\/[0-9]+', 'created_at' => $now],
            ['id' => 35]
        )
        // PUT 一鍵切換
        // sidebar: auto-scan
        ->updateOrInsert(
            ['method' => 'PUT', 'path_regex' => 'scan-platform\/change-all', 'created_at' => $now],
            ['id' => 36]
        )

/*
* =======
* Process
* =======
*/
        // GET get Process Result
        // sidebar: Domains
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'process\/result', 'created_at' => $now],
            ['id' => 37]
        )
        // GET get Process
        // sidebar: Domains
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'process', 'created_at' => $now],
            ['id' => 38]
        )


/*
* =======
* Users
* =======
*/
        // GET Get Permission
        // sidebar: Users
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'permissions', 'created_at' => $now],
            ['id' => 39]
        )
        // GET Get Role Permission By Role ID
        // sidebar: Users
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'roles\/[0-9]+\/permissions', 'created_at' => $now],
            ['id' => 40]
        )
        // POST Upsert Role Permission By Role ID
        // sidebar: Users
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'roles\/[0-9]+\/permissions', 'created_at' => $now],
            ['id' => 41]
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
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'config\/s3', 'created_at' => $now],
            ['id' => 42]
        )
        // GET Get Scanned Data (By Platform & CdnProvider)
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'scan-platform\/[0-9]+\/scanned-data', 'created_at' => $now],
            ['id' => 43]
        )
        // GET Get Scanned Data (All)
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'scan-platform\/scanned-data', 'created_at' => $now],
            ['id' => 44]
        )
        // GET Show Self Backup
        // sidebar: Config Backup
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'backups\/self', 'created_at' => $now],
            ['id' => 45]
        )
        // POST Create Backup
        // sidebar: Config Backup
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'backups', 'created_at' => $now],
            ['id' => 46]
        )
        // PUT Update Backup
        // sidebar: Config Backup
        ->updateOrInsert(
            ['method' => 'PUT', 'path_regex' => 'backups\/[0-9]+', 'created_at' => $now],
            ['id' => 47]
        )
        // GET Get Users
        // sidebar: Users
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'users', 'created_at' => $now],
            ['id' => 48]
        )
        // POST Create User
        // sidebar: Users
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'users', 'created_at' => $now],
            ['id' => 49]
        )
        // PUT User status
        // sidebar: Users
        ->updateOrInsert(
            ['method' => 'PUT', 'path_regex' => 'users\/[0-9]+\/status', 'created_at' => $now],
            ['id' => 50]
        )
        // PUT Update Profile
        // sidebar: Users
        ->updateOrInsert(
            ['method' => 'PUT', 'path_regex' => 'users\/[0-9]+\/profile', 'created_at' => $now],
            ['id' => 51]
        )
        // GET Get CDN Providers
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'cdn_providers', 'created_at' => $now],
            ['id' => 52]
        )
        // POST Create CDN Provider
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'cdn_providers', 'created_at' => $now],
            ['id' => 53]
        )
        // PATCH Edit CDN Provider
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['method' => 'PATCH', 'path_regex' => 'cdn_providers\/[0-9]+', 'created_at' => $now],
            ['id' => 54]
        )
        // PATCH 停止/回復 CDN
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['method' => 'PATCH', 'path_regex' => 'cdn_providers\/[0-9]+\/status', 'created_at' => $now],
            ['id' => 55]
        )
        // DELETE Delete CDN Provider
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['method' => 'DELETE', 'path_regex' => 'cdn_providers\/[0-9]+', 'created_at' => $now],
            ['id' => 56]
        )
        // GET Get Scan Platform
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'scan-platform', 'created_at' => $now],
            ['id' => 57]
        )
        // POST Create Scan Platform
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'scan-platform', 'created_at' => $now],
            ['id' => 58]
        )
        // PATCH Edit Scan Platform
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['method' => 'PATCH', 'path_regex' => 'scan-platform\/[0-9]+', 'created_at' => $now],
            ['id' => 59]
        )
        // DELETE Delete Scan Platform
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['method' => 'DELETE', 'path_regex' => 'scan-platform\/[0-9]+', 'created_at' => $now],
            ['id' => 60]
        )
        // POST Create Scanned Data
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['method' => 'POST', 'path_regex' => 'scan-platform\/[0-9]+\/scanned-data', 'created_at' => $now],
            ['id' => 61]
        )
        // GET Check Default CDN
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'cdn_providers', 'created_at' => $now],
            ['id' => 62]
        )
        // GET Scan CD 時間
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'scan-platform\/lock-time', 'created_at' => $now],
            ['id' => 63]
        )
        // GET Get Domain By Id
        // sidebar: Domains, Group, iRoueCDN
        ->updateOrInsert(
            ['method' => 'GET', 'path_regex' => 'domains\/[0-9]+', 'created_at' => $now],
            ['id' => 64]
        )

/*
* =====================
* 第 二 批 end
* =====================
*/
        ;
    }
}
