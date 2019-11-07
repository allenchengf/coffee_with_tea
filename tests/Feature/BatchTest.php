<?php

namespace Tests\Feature;

use Hiero7\Services\BatchService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Hiero7\Services\DnsProviderService;
use Faker\Factory as Faker;
use Hiero7\Models\CdnProvider;

class BatchTest extends TestCase
{
    use DatabaseMigrations;
    protected $batchService;
    protected $dnsprovider;

    protected $domains = [];
    protected $user;

    protected function setUp()
    {
        parent::setUp();
        $this->user = array("uuid" => \Illuminate\Support\Str::uuid(), "user_group_id" => 1);
        $this->dnsprovider =  $this->app->make('Hiero7\Services\DnsProviderService');
        $this->batchService = $this->app->make('Hiero7\Services\BatchService');

        $this->seed('CdnProviderSeeder');
    }

    public function tearDown()
    {
        $this->user = null;
        $this->domains = [];
        $this->batchService = null;
        $this->dnsprovider = null;
        parent::tearDown();
    }

    public function testBatchLarge(){
        $faker = Faker::create();
        $result = $this->batchService->store($this->domains, $this->user);
        for($i=0;$i<100;$i++){
            $domain = $faker->unique()->domainName;
            $this->domains[] = [
                'name' => 'justin'.$domain,
                'cdns'  => [[
                    "name"=> "Cloudflare",
                    "cname"=> $faker->unique()->domainName,
                ]],
            ];
        }
        $result = $this->batchService->store($this->domains, $this->user);
        $filtered = collect($result)->filter(function ($monitor) {
            return $monitor !== [];
        });

        // $this->assertEquals($filtered, collect([])); 保留原本想要測試的
        $this->assertArrayHasKey('success', $filtered);
        $this->assertArrayHasKey('failure', $filtered);
    }
}