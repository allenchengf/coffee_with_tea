## .env

```yaml

DB_DATABASE=coffee_with_tea

JWT_SECRET=s07mAuXcJUWZq3LZAiXrqjec6EEg2ZR5N97or1WytTONkjJwhfowVrK8eQzI1S5o

OPERATION_LOG_URL=leodock_backend gateway IP:39452/api/v1
OPERATION_LOG_SIZE=3000 (取得 Log 的上限)
USER_MODULE=leodock_backend gateway IP:35320/api/v1
DNS_PROVIDER_API=leodock_backend gateway IP:35341/api/v1

DNS_POD_DOMAIN=DNS Pod 購買的域名
DNS_POD_LOGIN_TOKEN=ID,Token
DNS_POD_DOMAIN_ID=

DOMAIN_REGULAR='regex:/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}?$/'
CDN_TTL=600
SCHEME=1 #dnspod free

PLATFORM_KEY=eu7nxsfttc

CONFIG_WAIT_TIME=2 (分鐘)
SCAN_SECOND=30 (爬蟲執行的時間 秒 - Justin)
SCAN_COOL_DOWN=50 (爬蟲的冷卻時間 分)

AWS_ACCESS_KEY_ID= (目前為 Config Backup 用 - Justin)
AWS_SECRET_ACCESS_KEY=
AWS_REGION=

S3_BUCKET_NAME_CONFIG_BACKUP= (S3 Bucket 名稱，目前為 Config Backup 用 - Justin)

BACKUP_AT=03:00 (沒設定 Backup 時間的 user_group 們其備份時間 - Justin)

QUEUE_CONNECTION=redis (原本是 sync 要改成 redis - Yuan)
```

## 部署確認
1. 重置 supversor
```
supervisorctl restart iroutecdn:*
```

#### Note

1. leodock_backend gateway IP :

請去 leodock .env 找尋 network 底下的 gateway

2. S3 Error: The difference between the request time and the current time is too large
```
# 校正時間
sudo ntpdate ntp.ubuntu.com
```

3. phpunit
```bash
# 測試覆蓋率
phpunit --coverage-html coverage
```

4. DB Seeder 

### 可重複執行
```
php artisan db:seed --class=ApisTableSeeder
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=ApiPermissionMappingTableSeeder
```

5. 排程

於容器內觸發 config backup 排程，指令記錄於 ./crontabConfigBackup。
```
crontab -e
```

若排程無執行，則指令開啟 cron：
```
service cron start
```