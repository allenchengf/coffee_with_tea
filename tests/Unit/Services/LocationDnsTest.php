<?php

namespace Tests\Unit\Services;

use Hiero7\Models\cdn;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationDnsSetting;
use Hiero7\Models\LocationNetwork;
use Hiero7\Repositories\CdnRepository;
use Hiero7\Repositories\LineRepository;use Hiero7\Repositories\LocationDnsSettingRepository;
use Hiero7\Services\DnsProviderService;use Hiero7\Services\LocationDnsSettingService;
use Tests\TestCase;

class LocationDnsTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed();
        $this->seed('LocationDnsSettingSeeder');
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');
        $this->dnsprovider = $this->initMock(DnsProviderService::class);
        app()->call([$this, 'repository']);
        app()->call([$this, 'mockery']);
        $this->domain = Domain::inRandomOrder()->first();
        $this->cdnId = $this->domain->cdns()->first()->id;
        $this->cdn = $this->domain->cdns()->where('id', $this->cdnId)->first();
        $this->locationNetwork = LocationNetwork::inRandomOrder()->first();
        $this->locationDnsSetting = LocationDnsSetting::first();
        $this->service = new LocationDnsSettingService($this->locationDnsSettingRepository, $this->dnsprovider, $this->lineRepository, $this->cdnRepository);

    }

    public function repository(LocationDnsSettingRepository $locationDnsSettingRepository, LineRepository $lineRepository, CdnRepository $cdnRepository)
    {
        $this->locationDnsSettingRepository = $locationDnsSettingRepository;
        $this->lineRepository = $lineRepository;
        $this->cdnRepository = $cdnRepository;
    }

    public function mockery()
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

        $this->dnsprovider->shouldReceive('deleteRecord')->withAnyArgs()
            ->andReturn(["message" => "Success", "errorCode" => null, "data" => []]);
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

        $response = $this->service->createSetting($data, $this->domain, $this->cdn, $this->locationNetwork);

        $this->assertEquals($response, true);

    }

    public function testUpdateDataExist()
    {
        $data = [
            "cdn_id" => $this->cdnId,
            "edited_by" => "de20afd0-d009-4fbf-a3b0-2c3257915d10",
        ];

        $response = $this->service->updateSetting($data, $this->domain, $this->cdn, $this->locationDnsSetting);

        $this->assertEquals($response, true);

    }

    /**
     * 情境一
     *
     * CDN 不是 Default
     * IRoute無有資料需要新增
     *
     * @return void
     */
    public function testDecideAction_Situation1()
    {
        $this->addUuidforPayload()
            ->addUserGroupId(1)
            ->setJwtTokenPayload(1, $this->jwtPayload);

        $this->domain = Domain::find(1);
        $this->cdn = Cdn::find(2);
        $this->locationNetwork = LocationNetwork::find(2);

        $response = $this->service->decideAction($this->cdn->cdn_provider_id, $this->domain, $this->locationNetwork);

        $this->assertEquals($response, true);
    }

    /**
     * 情境二
     *
     * CDN 不是 Default
     * 線路有設定需要更新
     *
     * @return void
     */
    public function testDecideAction_Situation2()
    {
        $this->addUuidforPayload()
            ->addUserGroupId(1)
            ->setJwtTokenPayload(1, $this->jwtPayload);

        $this->domain = Domain::find(1);
        $this->cdn = Cdn::find(2);
        $this->locationNetwork = LocationNetwork::find(1);

        $response = $this->service->decideAction($this->cdn->cdn_provider_id, $this->domain, $this->locationNetwork);

        $this->assertEquals($response, true);
    }

    /**
     * 情境三
     *
     * CDN 不是 Default
     * 線路有設定需要是正確的
     *
     * @return void
     */
    public function testDecideAction_Situation3()
    {
        $this->addUuidforPayload()
            ->addUserGroupId(1)
            ->setJwtTokenPayload(1, $this->jwtPayload);

        $this->domain = Domain::find(1);
        $this->cdn = Cdn::find(2);
        $this->locationNetwork = LocationNetwork::find(1);
        LocationDnsSetting::find(1)->update(['cdn_id' => $this->cdn->id]);
        $response = $this->service->decideAction($this->cdn->cdn_provider_id, $this->domain, $this->locationNetwork);

        $this->assertEquals($response, true);
    }

    /**
     * 情境四
     *
     * CDN 是 Default
     * 線路無設定
     *
     * @return void
     */
    public function testDecideAction_Situation4()
    {
        $this->addUuidforPayload()
            ->addUserGroupId(1)
            ->setJwtTokenPayload(1, $this->jwtPayload);

        $this->domain = Domain::find(1);
        $this->cdn = Cdn::find(1);
        $this->locationNetwork = LocationNetwork::find(2);

        $response = $this->service->decideAction($this->cdn->cdn_provider_id, $this->domain, $this->locationNetwork);

        $this->assertEquals($response, true);
    }

    /**
     * 情境五
     *
     * CDN 是 Default
     * 線路有設定需要刪除
     *
     * @return void
     */
    public function testDecideAction_Situation5()
    {
        $this->addUuidforPayload()
            ->addUserGroupId(1)
            ->setJwtTokenPayload(1, $this->jwtPayload);

        $this->domain = Domain::find(1);
        $this->cdn = Cdn::find(1);
        $this->locationNetwork = LocationNetwork::find(1);

        $response = $this->service->decideAction($this->cdn->cdn_provider_id, $this->domain, $this->locationNetwork);

        $this->assertEquals($response, true);
    }

}
