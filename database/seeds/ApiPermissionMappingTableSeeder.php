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
        ->updateOrCreate(
            ['id' => 1],
            ['permission_id' => 2, 'api_id' => 1, 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 2],
            ['permission_id' => 3, 'api_id' => 1, 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 3],
            ['permission_id' => 4, 'api_id' => 1, 'created_at' => $now]
        )
        // POST Create Domain
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 4],
            ['permission_id' => 2, 'api_id' => 2, 'created_at' => $now]
        )
        // POST Batch Create Domain & Cdn
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 5],
            ['permission_id' => 2, 'api_id' => 3, 'created_at' => $now]
        )
        // PUT Edit Domain
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 6],
            ['permission_id' => 2, 'api_id' => 4, 'created_at' => $now]
        )
        // DELETE Domain
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 7],
            ['permission_id' => 2, 'api_id' => 5, 'created_at' => $now]
        )

/*
    * =======
    * CDN
    * =======
*/
        // GET Get All
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 8],
            ['permission_id' => 2, 'api_id' => 6, 'created_at' => $now]
        )
        // POST Create
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 9],
            ['permission_id' => 2, 'api_id' => 7, 'created_at' => $now]
        )
        // PATCH Udpate Default
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 10],
            ['permission_id' => 2, 'api_id' => 8, 'created_at' => $now]
        )
        // PATCH Udpate Cname
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 11],
            ['permission_id' => 2, 'api_id' => 9, 'created_at' => $now]
        )
        // DELETE delete
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 12],
            ['permission_id' => 2, 'api_id' => 10, 'created_at' => $now]
        )

/*
* =======
* IRouteCDN
* =======
*/
        // GET Get iRoute
        // sidebar: Domain, iRoueCDN
        ->updateOrCreate(
            ['id' => 13],
            ['permission_id' => 2, 'api_id' => 11, 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 14],
            ['permission_id' => 4, 'api_id' => 11, 'created_at' => $now]
        )
        // PUT Edit Setting
        // sidebar: iRoueCDN
        ->updateOrCreate(
            ['id' => 15],
            ['permission_id' => 4, 'api_id' => 12, 'created_at' => $now]
        )
        // GET Get By Group/Domain
        // sidebar: iRoueCDN
        ->updateOrCreate(
            ['id' => 16],
            ['permission_id' => 4, 'api_id' => 13, 'created_at' => $now]
        )
        // GET Get Group's iRoute
        // sidebar: Grouping, iRoueCDN
        ->updateOrCreate(
            ['id' => 17],
            ['permission_id' => 3, 'api_id' => 14, 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 18],
            ['permission_id' => 4, 'api_id' => 14, 'created_at' => $now]
        )
        // PUT Edit Group's iRoute
        // sidebar: iRoueCDN
        ->updateOrCreate(
            ['id' => 19],
            ['permission_id' => 4, 'api_id' => 15, 'created_at' => $now]
        )
        // GET Get All iroute by Group/Domain
        // sidebar: iRoueCDN
        ->updateOrCreate(
            ['id' => 20],
            ['permission_id' => 4, 'api_id' => 16, 'created_at' => $now]
        )
        // GET Get All iroute by Group (pagination)
        // sidebar: iRoueCDN
        ->updateOrCreate(
            ['id' => 21],
            ['permission_id' => 4, 'api_id' => 17, 'created_at' => $now]
        )
        // GET Get All iroute by Domain (pagination)
        // sidebar: iRoueCDN
        ->updateOrCreate(
            ['id' => 22],
            ['permission_id' => 4, 'api_id' => 18, 'created_at' => $now]
        )
        // PATCH CDN Provider Scannable
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 23],
            ['permission_id' => 1, 'api_id' => 19, 'created_at' => $now]
        )
        // GET Get Group's Domains
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 24],
            ['permission_id' => 3, 'api_id' => 20, 'created_at' => $now]
        )
        // POST Create Domain To Group
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 25],
            ['permission_id' => 3, 'api_id' => 21, 'created_at' => $now]
        )
        // POST Batch Group's Add Domain
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 26],
            ['permission_id' => 3, 'api_id' => 22, 'created_at' => $now]
        )
        // PUT Edit Group's Default Cdn
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 27],
            ['permission_id' => 3, 'api_id' => 23, 'created_at' => $now]
        )
        // DELETE Delete Domain From Group
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 28],
            ['permission_id' => 3, 'api_id' => 24, 'created_at' => $now]
        )
        // GET Get Group
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 29],
            ['permission_id' => 3, 'api_id' => 25, 'created_at' => $now]
        )
        // POST Create Group
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 30],
            ['permission_id' => 3, 'api_id' => 26, 'created_at' => $now]
        )
        // PUT Edit Group
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 31],
            ['permission_id' => 3, 'api_id' => 27, 'created_at' => $now]
        )
        // DELETE Delete Group
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 32],
            ['permission_id' => 3, 'api_id' => 28, 'created_at' => $now]
        )

/*
* =======
* Operation Logs
* =======
*/
        // GET Get Operation All Logs
        // sidebar: Logs
        ->updateOrCreate(
            ['id' => 33],
            ['permission_id' => 5, 'api_id' => 29, 'created_at' => $now]
        )
        // GET Get Operation Logs by Category
        // sidebar: Logs
        ->updateOrCreate(
            ['id' => 34],
            ['permission_id' => 5, 'api_id' => 30, 'created_at' => $now]
        )
        // GET Get Operation Log Category List
        // sidebar: Logs
        ->updateOrCreate(
            ['id' => 35],
            ['permission_id' => 5, 'api_id' => 31, 'created_at' => $now]
        )

/*
* =======
* Config
* =======
*/
        // GET get Config
        // sidebar: Tool > Config Backup
        ->updateOrCreate(
            ['id' => 36],
            ['permission_id' => 7, 'api_id' => 32, 'created_at' => $now]
        )
        // POST import Config
        // sidebar: Tool > Config Backup
        ->updateOrCreate(
            ['id' => 37],
            ['permission_id' => 7, 'api_id' => 33, 'created_at' => $now]
        )

/*
* =======
* Auto Scan
* =======
*/
        // PUT 一鍵切換 By Domain
        // sidebar: auto-scan
        ->updateOrCreate(
            ['id' => 38],
            ['permission_id' => 6, 'api_id' => 34, 'created_at' => $now]
        )
        // PUT 一鍵切換 By Domain Group
        // sidebar: auto-scan
        ->updateOrCreate(
            ['id' => 39],
            ['permission_id' => 6, 'api_id' => 35, 'created_at' => $now]
        )
        // PUT 一鍵切換
        // sidebar: auto-scan
        ->updateOrCreate(
            ['id' => 40],
            ['permission_id' => 6, 'api_id' => 36, 'created_at' => $now]
        )

/*
* =======
* Process
* =======
*/
        // GET get Process Result
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 41],
            ['permission_id' => 2, 'api_id' => 37, 'created_at' => $now]
        )
        // GET get Process
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 42],
            ['permission_id' => 2, 'api_id' => 38, 'created_at' => $now]
        )

/*
* =======
* Users
* =======
*/
        // GET Get Permission
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 43],
            ['permission_id' => 8, 'api_id' => 39, 'created_at' => $now]
        )
        // GET Get Role Permission By Role ID
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 44],
            ['permission_id' => 8, 'api_id' => 40, 'created_at' => $now]
        )
        // POST Upsert Role Permission By Role ID
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 45],
            ['permission_id' => 8, 'api_id' => 41, 'created_at' => $now]
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

        // GET get list from S3
        // sidebar: Config
        ->updateOrCreate(
            ['id' => 46],
            ['permission_id' => 7, 'api_id' => 42, 'created_at' => $now]
        )
        // GET Get Scanned Data (By Platform & CdnProvider)
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 47],
            ['permission_id' => 6, 'api_id' => 43, 'created_at' => $now]
        )
        // GET Get Scanned Data (All)
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 48],
            ['permission_id' => 6, 'api_id' => 44, 'created_at' => $now]
        )
        // GET Show Self Backup
        // sidebar: Config Backup
        ->updateOrCreate(
            ['id' => 49],
            ['permission_id' => 7, 'api_id' => 45, 'created_at' => $now]
        )
        // POST Create Backup
        // sidebar: Config Backup
        ->updateOrCreate(
            ['id' => 50],
            ['permission_id' => 7, 'api_id' => 46, 'created_at' => $now]
        )
        // PUT Update Backup
        // sidebar: Config Backup
        ->updateOrCreate(
            ['id' => 51],
            ['permission_id' => 7, 'api_id' => 47, 'created_at' => $now]
        )
        // GET Get Users
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 52],
            ['permission_id' => 8, 'api_id' => 48, 'created_at' => $now]
        )
        // POST Create User
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 53],
            ['permission_id' => 8, 'api_id' => 49, 'created_at' => $now]
        )
        // PUT User status
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 54],
            ['permission_id' => 8, 'api_id' => 50, 'created_at' => $now]
        )
        // PUT Update Profile
        // sidebar: Users
        ->updateOrCreate(
            ['id' => 55],
            ['permission_id' => 8, 'api_id' => 51, 'created_at' => $now]
        )
        // GET Get CDN Providers
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 56],
            ['permission_id' => 1, 'api_id' => 52, 'created_at' => $now]
        )
        // POST Create CDN Provider
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 57],
            ['permission_id' => 1, 'api_id' => 53, 'created_at' => $now]
        )
        // PATCH Edit CDN Provider
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 58],
            ['permission_id' => 1, 'api_id' => 54, 'created_at' => $now]
        )
        // PATCH 停止/回復 CDN
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 59],
            ['permission_id' => 1, 'api_id' => 55, 'created_at' => $now]
        )
        // DELETE Delete CDN Provider
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 60],
            ['permission_id' => 1, 'api_id' => 56, 'created_at' => $now]
        )
        // GET Get Scan Platform
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 61],
            ['permission_id' => 6, 'api_id' => 57, 'created_at' => $now]
        )
        // POST Create Scan Platform
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 62],
            ['permission_id' => 6, 'api_id' => 58, 'created_at' => $now]
        )
        // PATCH Edit Scan Platform
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 63],
            ['permission_id' => 6, 'api_id' => 59, 'created_at' => $now]
        )
        // DELETE Delete Scan Platform
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 64],
            ['permission_id' => 6, 'api_id' => 60, 'created_at' => $now]
        )
        // POST Create Scanned Data
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 65],
            ['permission_id' => 6, 'api_id' => 61, 'created_at' => $now]
        )
        // GET Check Default CDN
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 66],
            ['permission_id' => 1, 'api_id' => 62, 'created_at' => $now]
        )
        // GET Scan CD 時間
        // sidebar: Auto Scan
        ->updateOrCreate(
            ['id' => 67],
            ['permission_id' => 6, 'api_id' => 63, 'created_at' => $now]
        )
        // GET Get Domain By Id
        // sidebar: Domains, Group, iRoueCDN
        ->updateOrCreate(
            ['id' => 68],
            ['permission_id' => 2, 'api_id' => 64, 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 69],
            ['permission_id' => 3, 'api_id' => 64, 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 70],
            ['permission_id' => 4, 'api_id' => 64, 'created_at' => $now]
        )

/*
* =====================
* 第 二 批 end
* =====================
*/



/*
 * =====================
 * 第 三 批 start
 * 2020-01-07
 * =====================
*/
        // GET Get CDN Providers
        // sidebar: CDN Providers
        ->updateOrCreate(
            ['id' => 71],
            ['permission_id' => 2, 'api_id' => 52, 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 72],
            ['permission_id' => 3, 'api_id' => 52, 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 73],
            ['permission_id' => 4, 'api_id' => 52, 'created_at' => $now]
        )
        ->updateOrCreate(
            ['id' => 74],
            ['permission_id' => 6, 'api_id' => 52, 'created_at' => $now]
        )
        // GET Get Group's Domains
        // sidebar: Grouping
        ->updateOrCreate(
            ['id' => 75],
            ['permission_id' => 4, 'api_id' => 20, 'created_at' => $now]
        )
        // GET show one from S3
        // sidebar: Config
        ->updateOrCreate(
            ['id' => 76],
            ['permission_id' => 7, 'api_id' => 65, 'created_at' => $now]
        )
        // POST create Config
        // sidebar: Config
        ->updateOrCreate(
            ['id' => 77],
            ['permission_id' => 7, 'api_id' => 66, 'created_at' => $now]
        )
        // PUT restore Config
        // sidebar: Config
        ->updateOrCreate(
            ['id' => 78],
            ['permission_id' => 7, 'api_id' => 67, 'created_at' => $now]
        )
        // POST Check And Sync Record
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 79],
            ['permission_id' => 2, 'api_id' => 68, 'created_at' => $now]
        )
        // POST Check And Sync Record
        // sidebar: Domains
        ->updateOrCreate(
            ['id' => 80],
            ['permission_id' => 2, 'api_id' => 69, 'created_at' => $now]
        )

/*
* =====================
* 第 三 批 end
* =====================
*/

        ;
    }
}
