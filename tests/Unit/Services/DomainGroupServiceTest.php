<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hiero7\Services\{LocationDnsSettingService,CdnService,DomainGroupService};
use Hiero7\Repositories\DomainGroupRepository;
use Hiero7\Models\{DomainGroup};
use Illuminate\Support\Facades\Artisan;


class DomainGroupServiceTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
        Artisan::call('db:seed');
        app()->call([$this, 'repository']);
        app()->call([$this, 'serviceMock']);
        $this->seed('LocationDnsSettingSeeder');
        $this->service = new DomainGroupService($this->domainGroupRepository, $this->cdnService, $this->locationDnsSettingService);
        $this->domainGroup = DomainGroup::find(1);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function repository(DomainGroupRepository $domainGroupRepository)
    {
        $this->domainGroupRepository = $domainGroupRepository;
        $this->cdnService = $this->initMock(CdnService::class);
        $this->locationDnsSettingService = $this->initMock(LocationDnsSettingService::class);
    }

    public function serviceMock()
    {
        $this->cdnService->shouldReceive('changeDefaultToTrue')->withAnyArgs()->andReturn(true);
        $this->locationDnsSettingService->shouldReceive('updateSetting')->withAnyArgs()->andReturn(true);
        $this->locationDnsSettingService->shouldReceive('createSetting')->withAnyArgs()->andReturn(true);

    }

    public function testCreateDomainToGroup()
    {
        $request = ['domain_id' => 2,
                    'edited_by' => 'de20afd0-d009-4fbf-a3b0-2c3257915d10'];
        $response = $this->service->createDomainToGroup($request,$this->domainGroup);

        $this->assertNotEmpty($response);
    }
}
