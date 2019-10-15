<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\Log;
use App;

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
        $configController = App::make('App\Http\Controllers\Api\v1\ConfigController');
        $callback = $configController->storeBackup();
        
        Log::info('[ConfigBackup] ' . json_encode($callback));
    }
}