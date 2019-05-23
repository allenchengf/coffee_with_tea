<?php

namespace Tests\Unit;

use Tests\TestCase;
use Hiero7\Services\LocationDnsSettingService;
use Hiero7\Repositories\LineRepository;
use Hiero7\Services\DnsProviderService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Illuminate\Support\Facades\Artisan;

class LocationDnsTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
        Artisan::call('db:seed');
        $this->seed('LocationDnsSettingSeeder');
        $this->dnsprovider = $this->initMock(DnsProviderService::class);
        app()->call([$this, 'repository']);  
        app()->call([$this,'dnsPodMock']);  
        $this->service = new LocationDnsSettingService($this->locationDnsSettingRepository,$this->dnsprovider,$this->lineRepository);

    }

    public function repository(LocationDnsSettingRepository $locationDnsSettingRepository, LineRepository $lineRepository)
    {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
        $this->lineRepository = $lineRepository;
    }

    public function dnsPodMock()
    {
        $this->dnsprovider->shouldReceive('createRecord')->withAnyArgs()
                                ->andReturn(["message" =>"Success","errorCode"=>null,"data" => [
                                            "record" => [
                                                "id" => "426118405",
                                                "name" => "hiero7.test1.com",
                                                "status" => "enabled",
                                                "weight" => null
                                    ]]]);
        $this->dnsprovider->shouldReceive('editRecord')->withAnyArgs()
                                ->andReturn(["message" =>"Success","errorCode"=>null,"data" => [
                                        "record" => [
                                            "id" => "426278576",
                                            "name" => "hiero7.test1.com",
                                            "value" => "cCnPjg.com.",
                                            "status" => "enable",
                                            "weight" => null
                                    ]]]);
}

    public function tearDown()
    {
        $this->service = null;
        parent::tearDown();
    }

    public function testFormatCreateRecord()
    {
        $domainId = 1;
        $locationNetworkRid = 1;

        $data = [
            "continent_id" => 1,
            "country_id" => 2,
            "network_id" => 2,
            "cdn_id" => 2,
            "edited_by" => "de20afd0-d009-4fbf-a3b0-2c3257915d10"
        ];

        $result = $this->service->formatData($data,$domainId,$locationNetworkRid,'create');

        $response = [
            "domain_id" => $domainId,
            "domain_cname" => "hiero7.test1.com",
            "cdn_id" => 2,
            "cdn_cname" => "dnspod.com",
            "network_name" => "国外",
            "location_networks_id" => $locationNetworkRid,
            "edited_by" => "de20afd0-d009-4fbf-a3b0-2c3257915d10"
        ];

        $this->assertEquals($result,$response);
    }

    public function testCreateRecord()
    {
        $domain = 1;
        $locationNetworkRid = 1;

        $data = [
            "continent_id" => 1,
            "country_id" => 2,
            "network_id" => 2,
            "cdn_id" => 2,
            "edited_by" => "de20afd0-d009-4fbf-a3b0-2c3257915d10"
        ];

        $response = $this->service->createSetting($data,$domain,$locationNetworkRid);

        $this->assertEquals($response,true);

    }

    public function testUpdateSetting()
    {
        $domain = 1;
        $locationNetworkRid = 1;

        $data = [
            "continent_id" => 1,
            "country_id" => 2,
            "network_id" => 2,
            "cdn_id" => 2,
            "edited_by" => "de20afd0-d009-4fbf-a3b0-2c3257915d10"
        ];

        $response = $this->service->updateSetting($data,$domain,$locationNetworkRid);

        $this->assertEquals($response,true);

    }
}

