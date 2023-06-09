# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.12.1] - 2020-03-12
### Fixed
- Route-rules List API
 
## [0.12.0] - 2020-03-12
### Add
- Dashboard Page Count Domain
- Get domain query optimization

## [0.11.10] - 2020-03-09
### Changed
- 排程處理方式
- Batch Create Domain & CDN 不產生 worker job

### Fixed
- Create CDN Bug

## [0.11.9] - 2020-03-03
### Changed
- queue 等待時間
- Save Log timeout
- Check DnsPod Middleware 調整

## [0.11.8] - 2020-02-26
### Fixed
- 中文域名 Mapping Error Bug
- 優化 Check-sync All DNS Pod Record 效能

## [0.11.7] - 2020-02-14
### Fixed
- Delete Domain Error Message

## [0.11.6] - 2020-02-14
### Fixed
- Delete Domain Error

## [0.11.5] - 2020-02-13
### Fixed
- 對 Dns Provider API Timeout 時間延長

## [0.11.4] - 2020-02-11
### Fixed
- CDN Provider Status / TTL Bug

## [0.11.3] - 2020-01-30
### Fixed
- Batch Domain Error Message
- Batch Domain Add Operation Log for job

## [0.11.2] - 2020-01-21
### Fixed
- Batch Domain Error Message

## [0.11.1] - 2020-01-17
### Fixed
- Batch Domain Error
- Config restore can't operation

## [0.11.0] - 2020-01-16
### Add
- command sync:dnspod-record
- Batch Group's Add Domain Log

## [0.10.3] - 2020-01-15
### Fixed
- Check DB And DNS Pod Sync API
- backup / restart config

## [0.10.2] - 2020-01-10
### Fixed
- Batch Domain Cant be completed
- ApiPermissionMapping Data
- Backup Config API
- Get Operation Logs by Category API

## [0.10.1] - 2020-01-07
### Fixed
- Header bug， Mac / Linux differences

## [0.10.0] - 2020-01-03
### Add
- Domain Pin Table
- Domain Pin CRD API

### Fixed
- RBAC - Delete Bug

## [0.9.0] - 2019-12-23
### Add
- RBAC - Migration
- RBAC - Seeder
- RBAC - APIs
- RBAC - Middleware (RolePermission)

### Fixed
- Add Domain to Group API Bug
- Operation Log Save IP Bug

## [0.8.2] - 2019-12-09
### Fixed
- Batch Domain & CDN Logic

### Changed
- Batch Domain & CDN Result
- Get Log for new format
- Log save format Array To Json 

## [0.8.1] - 2019-11-25
### Fixed
- create cdn_provider for insert

## [0.8.0] - 2019-11-25
### Added
- 批次新增取得結果 API
- 儲存 Log method

### Upate
- 更新 OperationLogTrait
- Log save for new format 

## [0.7.0] - 2019-11-08
### Added
- 查詢 Job Process 進度

### Upate
- Supervisor 的 conf 新增 --queue = woker
- Domain CNAME 後尾的點可自動移除

### Fixed
- 修改 Batch Add Domain & CDN 改由 Job 觸發

## [0.6.0] - 2019-10-28
### Added
- scan cooling time

### Fixed
- 更新備份 Config 功能
- 調整 Ｍiddleware check.dnspod

## [0.5.0] - 2019-10-09
### Added
- Backup 排程
- Backup 時間設定 CRU
- 新增 location_networks status 欄位
- 新增 switch Region
- 新增 Network Create, Delete API

### Fixed
- 修改 Get Last Scan Log Logic
- 修改 Save Scan 
- 修改 Batch Create Domain In to Group Bug
- 修改 Batch Create Domain & CDN Bug
- 修改 Edit iRouteCDN Region Output

## [0.4.3] - 2019-10-03
### Fixed
- 修改 Get Last Scan Log Output 格式

## [0.4.2] - 2019-10-03
### Fixed
- 修改 Get Last Scan Log Output 格式

## [0.4.1] - 2019-10-01
### Fixed
- 修改 Create Scanned 的排序

## [0.4.0] - 2019-09-27
### Added
- 一鍵切換 All
- Get Last Scan Log

### Changed
- Scan Data mapping funciton change to Service
- Delete CDN Provider Function
- Check Default CDN output add param

### Removed
- 根據 Region 選擇 A 切換至 B CDN Provider

### Fixed
- Domain Cname 轉小寫英文

## [0.3.3] - 2019-09-11
### Fixed
- Domain added to group Bug Fix

## [0.3.2] - 2019-09-09
### Fixed
- 將 LocationNetworkTableSeeder Data 設定補齊

## [0.3.1] - 2019-09-06
### Fixed
- 將 Get get continent list API 移除 internal.group Middleware
- 將 Get country list API 移除 internal.group Middleware

## [0.3.0] - 2019-08-30
### Added
- Add Scannable Column at CDN Provider Table
- Create Scan Log Table
- 儲存每次爬蟲的結果
- 輸出最後一次爬蟲的結果 API
- 一鍵切換 By Domain API
- 一鍵切換 By Domain Group API
- Get Domain 分頁 API
- Get iRouteCDN By Domain 分頁 API
- Get iRouteCDN By Domain Group 分頁 API

### Fixed
- 優化切換線路 Method
- 優化切換線路 Test 情境
- 重構 Check Default CDN
- Validation 輸出格式調整
- 修復 Delete Region Bug

## [0.2.0] - 2019-08-16
### Added
- CRUD Scan Platform Table 
- 串接檢測平台爬蟲
- 檢測平台爬蟲資料 Mapping 表
- 根據 Region 選擇 A 切換至 B CDN Provider

### Fixed
- 優化 Region 切換 CDN Provider 的 Logic

## [0.1.5] - 2019-08-01
### Fixed
- Change CDN Provider TTL No Include Location Dns Setting

## [0.1.4] - 2019-08-01
### Fixed
- Get Route-Rules By Domain Data Error

## [0.1.3] - 2019-07-30
### Fixed
- Edit Route-Rules By Domain Error

## [0.1.2] - 2019-07-30
### Fixed
- Get All Route-Rules API 效能

## [0.1.1] - 2019-07-25
### Fixed
- 將 Domain 加入 Group 時 ， DnsPod Record Different Bug
- Change IRoute Network 的 cdn_Provider 時 ， DnsPod Record Different Bug
- 只能看到 Login User Group 的 Domain Group
- DB Sync To DnsPod Record Bug

## [0.1.0] - 2019-07-24
### Added
- 新刪改查 Domain, CDN Provider, Cdn, Domain Group, IRoute Setting, Network
- 可依照 Domain Group 同步修改 Group 內的設定
- 查詢 / 寫入 Log
- 匯入 / 匯出 Config
