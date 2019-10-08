## .env

```yaml

DB_DATABASE=coffee_with_tea

JWT_SECRET=s07mAuXcJUWZq3LZAiXrqjec6EEg2ZR5N97or1WytTONkjJwhfowVrK8eQzI1S5o

OPERATION_LOG_URL=leodock_backend gateway IP:39452/api/v1
OPERATION_LOG_SIZE=3000
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
SCAN_SECOND=30 (爬蟲執行的時間 秒)
SCAN_LOG_INTERVAL=30 (取得最後一筆 log，往前推算的秒數，時間區間內之 logs)
```

#### Note

leodock_backend gateway IP :

請去 leodock .env 找尋 network 底下的 gateway


#### Database

For Production

```bash
php artisan db:seed --class=SchemeTableSeeder
php artisan db:seed --class=ContinentTableSeeder
php artisan db:seed --class=CountryTableSeeder
php artisan db:seed --class=NetworkTableSeeder
php artisan db:seed --class=LocationNetworkTableSeeder
```
