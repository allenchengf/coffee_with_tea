<?php

use Illuminate\Database\Seeder;
use Hiero7\Models\ApiPermissionMapping;

class ApiPermissionMappingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(ApiPermissionMapping $apiPermissionMapping)
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
        $apiPermissionMapping
        // Get Domain (pagination)
        // sidebar: Domains,Group,iRoueCDN
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 1, 'created_at' => $now],
            ['id' => 1]
        )
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 1, 'created_at' => $now],
            ['id' => 2]
        )
        ->updateOrInsert(
            ['permission_id' => 4, 'api_id' => 1, 'created_at' => $now],
            ['id' => 3]
        )
        // POST Create Domain
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 2, 'created_at' => $now],
            ['id' => 4]
        )
        // POST Batch Create Domain & Cdn
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 3, 'created_at' => $now],
            ['id' => 5]
        )
        // PUT Edit Domain
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 4, 'created_at' => $now],
            ['id' => 6]
        )
        // DELETE Domain
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 5, 'created_at' => $now],
            ['id' => 7]
        )

/*
    * =======
    * CDN
    * =======
*/
        // GET Get All
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 6, 'created_at' => $now],
            ['id' => 8]
        )
        // POST Create
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 7, 'created_at' => $now],
            ['id' => 9]
        )
        // PATCH Udpate Default
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 8, 'created_at' => $now],
            ['id' => 10]
        )
        // PATCH Udpate Cname
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 9, 'created_at' => $now],
            ['id' => 11]
        )
        // DELETE delete
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 10, 'created_at' => $now],
            ['id' => 12]
        )

/*
* =======
* IRouteCDN
* =======
*/
        // GET Get iRoute
        // sidebar: Domain, iRoueCDN
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 11, 'created_at' => $now],
            ['id' => 13]
        )
        ->updateOrInsert(
            ['permission_id' => 4, 'api_id' => 11, 'created_at' => $now],
            ['id' => 14]
        )
        // PUT Edit Setting
        // sidebar: iRoueCDN
        ->updateOrInsert(
            ['permission_id' => 4, 'api_id' => 12, 'created_at' => $now],
            ['id' => 15]
        )
        // GET Get By Group/Domain
        // sidebar: iRoueCDN
        ->updateOrInsert(
            ['permission_id' => 4, 'api_id' => 13, 'created_at' => $now],
            ['id' => 16]
        )
        // GET Get Group's iRoute
        // sidebar: Grouping, iRoueCDN
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 14, 'created_at' => $now],
            ['id' => 17]
        )
        ->updateOrInsert(
            ['permission_id' => 4, 'api_id' => 14, 'created_at' => $now],
            ['id' => 18]
        )
        // PUT Edit Group's iRoute
        // sidebar: iRoueCDN
        ->updateOrInsert(
            ['permission_id' => 4, 'api_id' => 15, 'created_at' => $now],
            ['id' => 19]
        )
        // GET Get All iroute by Group/Domain
        // sidebar: iRoueCDN
        ->updateOrInsert(
            ['permission_id' => 4, 'api_id' => 16, 'created_at' => $now],
            ['id' => 20]
        )
        // GET Get All iroute by Group (pagination)
        // sidebar: iRoueCDN
        ->updateOrInsert(
            ['permission_id' => 4, 'api_id' => 17, 'created_at' => $now],
            ['id' => 21]
        )
        // GET Get All iroute by Domain (pagination)
        // sidebar: iRoueCDN
        ->updateOrInsert(
            ['permission_id' => 4, 'api_id' => 18, 'created_at' => $now],
            ['id' => 22]
        )
        // PATCH CDN Provider Scannable
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['permission_id' => 1, 'api_id' => 19, 'created_at' => $now],
            ['id' => 23]
        )
        // GET Get Group's Domains
        // sidebar: Grouping
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 20, 'created_at' => $now],
            ['id' => 24]
        )
        // POST Create Domain To Group
        // sidebar: Grouping
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 21, 'created_at' => $now],
            ['id' => 25]
        )
        // POST Batch Group's Add Domain
        // sidebar: Grouping
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 22, 'created_at' => $now],
            ['id' => 26]
        )
        // PUT Edit Group's Default Cdn
        // sidebar: Grouping
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 23, 'created_at' => $now],
            ['id' => 27]
        )
        // DELETE Delete Domain From Group
        // sidebar: Grouping
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 24, 'created_at' => $now],
            ['id' => 28]
        )
        // GET Get Group
        // sidebar: Grouping
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 25, 'created_at' => $now],
            ['id' => 29]
        )
        // POST Create Group
        // sidebar: Grouping
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 26, 'created_at' => $now],
            ['id' => 30]
        )
        // PUT Edit Group
        // sidebar: Grouping
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 27, 'created_at' => $now],
            ['id' => 31]
        )
        // DELETE Delete Group
        // sidebar: Grouping
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 28, 'created_at' => $now],
            ['id' => 32]
        )

/*
* =======
* Operation Logs
* =======
*/
        // GET Get Operation All Logs
        // sidebar: Logs
        ->updateOrInsert(
            ['permission_id' => 5, 'api_id' => 29, 'created_at' => $now],
            ['id' => 33]
        )
        // GET Get Operation Logs by Category
        // sidebar: Logs
        ->updateOrInsert(
            ['permission_id' => 5, 'api_id' => 30, 'created_at' => $now],
            ['id' => 34]
        )
        // GET Get Operation Log Category List
        // sidebar: Logs
        ->updateOrInsert(
            ['permission_id' => 5, 'api_id' => 31, 'created_at' => $now],
            ['id' => 35]
        )

/*
* =======
* Config
* =======
*/
        // GET get Config
        // sidebar: Tool > Config Backup
        ->updateOrInsert(
            ['permission_id' => 7, 'api_id' => 32, 'created_at' => $now],
            ['id' => 36]
        )
        // POST import Config
        // sidebar: Tool > Config Backup
        ->updateOrInsert(
            ['permission_id' => 7, 'api_id' => 33, 'created_at' => $now],
            ['id' => 37]
        )

/*
* =======
* Auto Scan
* =======
*/
        // PUT 一鍵切換 By Domain
        // sidebar: auto-scan
        ->updateOrInsert(
            ['permission_id' => 6, 'api_id' => 34, 'created_at' => $now],
            ['id' => 38]
        )
        // PUT 一鍵切換 By Domain Group
        // sidebar: auto-scan
        ->updateOrInsert(
            ['permission_id' => 6, 'api_id' => 35, 'created_at' => $now],
            ['id' => 39]
        )
        // PUT 一鍵切換
        // sidebar: auto-scan
        ->updateOrInsert(
            ['permission_id' => 6, 'api_id' => 36, 'created_at' => $now],
            ['id' => 40]
        )

/*
* =======
* Process
* =======
*/
        // GET get Process Result
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 37, 'created_at' => $now],
            ['id' => 41]
        )
        // GET get Process
        // sidebar: Domains
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 38, 'created_at' => $now],
            ['id' => 42]
        )

/*
* =======
* Users
* =======
*/
        // GET Get Permission
        // sidebar: Users
        ->updateOrInsert(
            ['permission_id' => 8, 'api_id' => 39, 'created_at' => $now],
            ['id' => 43]
        )
        // GET Get Role Permission By Role ID
        // sidebar: Users
        ->updateOrInsert(
            ['permission_id' => 8, 'api_id' => 40, 'created_at' => $now],
            ['id' => 44]
        )
        // POST Upsert Role Permission By Role ID
        // sidebar: Users
        ->updateOrInsert(
            ['permission_id' => 8, 'api_id' => 41, 'created_at' => $now],
            ['id' => 45]
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
            ['permission_id' => 7, 'api_id' => 42, 'created_at' => $now],
            ['id' => 46]
        )
        // GET Get Scanned Data (By Platform & CdnProvider)
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['permission_id' => 6, 'api_id' => 43, 'created_at' => $now],
            ['id' => 47]
        )
        // GET Get Scanned Data (All)
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['permission_id' => 6, 'api_id' => 44, 'created_at' => $now],
            ['id' => 48]
        )
        // GET Show Self Backup
        // sidebar: Config Backup
        ->updateOrInsert(
            ['permission_id' => 7, 'api_id' => 45, 'created_at' => $now],
            ['id' => 49]
        )
        // POST Create Backup
        // sidebar: Config Backup
        ->updateOrInsert(
            ['permission_id' => 7, 'api_id' => 46, 'created_at' => $now],
            ['id' => 50]
        )
        // PUT Update Backup
        // sidebar: Config Backup
        ->updateOrInsert(
            ['permission_id' => 7, 'api_id' => 47, 'created_at' => $now],
            ['id' => 51]
        )
        // GET Get Users
        // sidebar: Users
        ->updateOrInsert(
            ['permission_id' => 8, 'api_id' => 48, 'created_at' => $now],
            ['id' => 52]
        )
        // POST Create User
        // sidebar: Users
        ->updateOrInsert(
            ['permission_id' => 8, 'api_id' => 49, 'created_at' => $now],
            ['id' => 53]
        )
        // PUT User status
        // sidebar: Users
        ->updateOrInsert(
            ['permission_id' => 8, 'api_id' => 50, 'created_at' => $now],
            ['id' => 54]
        )
        // PUT Update Profile
        // sidebar: Users
        ->updateOrInsert(
            ['permission_id' => 8, 'api_id' => 51, 'created_at' => $now],
            ['id' => 55]
        )
        // GET Get CDN Providers
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['permission_id' => 1, 'api_id' => 52, 'created_at' => $now],
            ['id' => 56]
        )
        // POST Create CDN Provider
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['permission_id' => 1, 'api_id' => 53, 'created_at' => $now],
            ['id' => 57]
        )
        // PATCH Edit CDN Provider
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['permission_id' => 1, 'api_id' => 54, 'created_at' => $now],
            ['id' => 58]
        )
        // PATCH 停止/回復 CDN
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['permission_id' => 1, 'api_id' => 55, 'created_at' => $now],
            ['id' => 59]
        )
        // DELETE Delete CDN Provider
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['permission_id' => 1, 'api_id' => 56, 'created_at' => $now],
            ['id' => 60]
        )
        // GET Get Scan Platform
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['permission_id' => 6, 'api_id' => 57, 'created_at' => $now],
            ['id' => 61]
        )
        // POST Create Scan Platform
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['permission_id' => 6, 'api_id' => 58, 'created_at' => $now],
            ['id' => 62]
        )
        // PATCH Edit Scan Platform
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['permission_id' => 6, 'api_id' => 59, 'created_at' => $now],
            ['id' => 63]
        )
        // DELETE Delete Scan Platform
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['permission_id' => 6, 'api_id' => 60, 'created_at' => $now],
            ['id' => 64]
        )
        // POST Create Scanned Data
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['permission_id' => 6, 'api_id' => 61, 'created_at' => $now],
            ['id' => 65]
        )
        // GET Check Default CDN
        // sidebar: CDN Providers
        ->updateOrInsert(
            ['permission_id' => 1, 'api_id' => 62, 'created_at' => $now],
            ['id' => 66]
        )
        // GET Scan CD 時間
        // sidebar: Auto Scan
        ->updateOrInsert(
            ['permission_id' => 6, 'api_id' => 63, 'created_at' => $now],
            ['id' => 67]
        )
        // GET Get Domain By Id
        // sidebar: Domains, Group, iRoueCDN
        ->updateOrInsert(
            ['permission_id' => 2, 'api_id' => 64, 'created_at' => $now],
            ['id' => 68]
        )
        ->updateOrInsert(
            ['permission_id' => 3, 'api_id' => 64, 'created_at' => $now],
            ['id' => 69]
        )
        ->updateOrInsert(
            ['permission_id' => 4, 'api_id' => 64, 'created_at' => $now],
            ['id' => 70]
        )

/*
* =====================
* 第 二 批 end
* =====================
*/

        ;
    }
}
