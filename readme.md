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
```

#### Note

leodock_backend gateway IP :

請去 leodock .env 找尋 network 底下的 gateway
