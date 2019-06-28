<?php

namespace Tests\Unit\Services;

use Hiero7\Repositories\LineRepository;
use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Services\DnsProviderService;
use Hiero7\Services\LocationDnsSettingService;
use Hiero7\Models\{LocationNetwork,cdn};
use Hiero7\Models\{Domain,LocationDnsSetting};
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LocationDnsTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
        $this->seed();
        $this->seed('LocationDnsSettingSeeder');
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');
        $this->dnsprovider = $this->initMock(DnsProviderService::class);
        app()->call([$this, 'repository']);
        app()->call([$this, 'dnsPodMock']);
        $this->domain = Domain::inRandomOrder()->first();
        $this->cdnId = $this->domain->cdns()->first()->id;
        $this->cdn = $this->domain->cdns()->where('id', $this->cdnId)->first();
        $this->locationNetwork = LocationNetwork::inRandomOrder()->first();
        $this->locationDnsSetting = LocationDnsSetting::first();
        $this->service = new LocationDnsSettingService($this->locationDnsSettingRepository, $this->dnsprovider, $this->lineRepository);

    }

    public function repository(LocationDnsSettingRepository $locationDnsSettingRepository, LineRepository $lineRepository)
    {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
        $this->lineRepository = $lineRepository;
    }

    public function dnsPodMock()
    {
        $this->dnsprovider->shouldReceive('createRecord')->withAnyArgs()
            ->andReturn(["message" => "Success", "errorCode" => null, "data" => [
                "record" => [
                    "id" => "426118405",
                    "name" => "hiero7.test1.com",
                    "status" => "enabled",
                    "weight" => null,
                ]]]);
        $this->dnsprovider->shouldReceive('editRecord')->withAnyArgs()
            ->andReturn(["message" => "Success", "errorCode" => null, "data" => [
                "record" => [
                    "id" => "426278576",
                    "name" => "hiero7.test1.com",
                    "value" => "cCnPjg.com.",
                    "status" => "enable",
                    "weight" => null,
                ]]]);
    }

    public function tearDown()
    {
        $this->service = null;
        parent::tearDown();
    }

    public function testCreateRecord()
    {
        $data = [
            "cdn_id" => $this->cdnId,
            "edited_by" => "de20afd0-d009-4fbf-a3b0-2c3257915d10",
        ];

        $response = $this->service->createSetting($data, $this->domain, $this->cdn ,$this->locationNetwork);

        $this->assertEquals($response, true);

    }

    public function testUpdateDataExist()
    {
        $data = [
            "cdn_id" => $this->cdnId,
            "edited_by" => "de20afd0-d009-4fbf-a3b0-2c3257915d10",
        ];

        $response = $this->service->updateSetting($data, $this->domain,$this->cdn ,$this->locationDnsSetting);

        $this->assertEquals($response, true);

    }
}