# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
