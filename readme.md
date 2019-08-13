#### .env

```yaml

DB_DATABASE=coffee_with_tea

JWT_SECRET=s07mAuXcJUWZq3LZAiXrqjec6EEg2ZR5N97or1WytTONkjJwhfowVrK8eQzI1S5o

KONG_OPERATION_LOG=http://10.88.55.124/operation_log
USER_MODULE=http://10.88.55.122:35320/api/v1
DNS_PROVIDER_API=http://10.88.55.122:35341/api/v1

DNS_PROVIDER_API=http://10.88.55.122:35341/api/v1
DNS_POD_DOMAIN=shiftcdn.com
DNS_POD_LOGIN_TOKEN=ID,Token
DNS_POD_DOMAIN_ID=

DOMAIN_REGULAR='regex:/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}?$/'
CDN_TTL=600
SCHEME=1 #dnspod free

OPERATION_LOG_URL=http://10.88.55.122:39452/api/v1
PLATFORM_KEY=eu7nxsfttc

CONFIG_WAIT_TIME=2 (分鐘)

SCANE_PROVIDER_17CE=
SCANE_PROVIDER_CHINAZ=
```

#### Database

For Production

```bash
php artisan db:seed --class=SchemeTableSeeder
php artisan db:seed --class=ContinentTableSeeder
php artisan db:seed --class=CountryTableSeeder
php artisan db:seed --class=NetworkTableSeeder
php artisan db:seed --class=LocationNetworkTableSeeder
```
