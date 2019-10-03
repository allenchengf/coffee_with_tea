# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
