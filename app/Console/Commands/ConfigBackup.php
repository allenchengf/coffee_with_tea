<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Log;

class ConfigBackup extends Command
{
    // 命令名稱
    protected $signature = 'backup:config';

    // 說明文字
    protected $description = '[backup] config';

    public function __construct()
    {
        parent::__construct();
    }

    // Console 執行的程式
    public function handle()
    {
        $appUrl = env('APP_URL', null);
        $userModuleUrl = env('USER_MODULE', null);
        if (! $appUrl || ! $userModuleUrl) {
            Log::info('[ConfigBackup: Notice] .env > APP_URL or USER_MODULE');
        }
        $appUrl .= '/api/v1';

        // login
        $email = "brian@123.com";
        $password = "1qaz@WSX";
        $unique_id = "hiero7";
        $key = "eu7nxsfttc";
    
        $response = Curl::to($userModuleUrl . '/login')
                        ->withData(compact('email', 'password', 'unique_id', 'key'))
                        ->asJson(true)
                        ->post();

        if (! $response || ! empty($response['error'])) {
            Log::info('[ConfigBackup: Fatal] userModule > login fail');
        }
        $authorization = "bearer " . (string) $response['data']['token'];
        
        // exec
        $callback = Curl::to( $appUrl . '/config/cronjob' )
                        ->withHeaders(['Authorization: ' . $authorization])
                        ->asJson( true )
                        ->get();
        Log::info('[ConfigBackup] ' . json_encode($callback));
    }
}