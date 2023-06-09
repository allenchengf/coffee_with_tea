<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hiero7\Services\{LocationDnsSettingService, CdnService, DomainGroupService};
use Hiero7\Repositories\{DomainGroupRepository, CdnRepository};
use Hiero7\Models\DomainGroup;
use App\Http\Requests\DomainGroupRequest;



class DomainGroupServiceTest extends TestCase
{   
    protected function setUp()
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->seed();
        $this->seed('LocationDnsSettingSeeder');
        $this->seed('DomainGroupTableSeeder');
        $this->seed('DomainGroupMappingTableSeeder');
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');
        app()->call([$this, 'repository']);
        app()->call([$this, 'serviceMock']);
        $this->service = new DomainGroupService($this->domainGroupRepository, $this->cdnRepository, $this->cdnService, $this->locationDnsSettingService);
        $this->domainGroup = DomainGroup::find(1);
    }

    protected function tearDown()
    {
        $this->service = null;
        $this->domainGroup = null;
        parent::tearDown();
    }

    public function repository(DomainGroupRepository $domainGroupRepository, CdnRepository $cdnRepository)
    {
        $this->domainGroupRepository = $domainGroupRepository;
        $this->cdnRepository = $cdnRepository;
        $this->cdnService = $this->initMock(CdnService::class);
        $this->locationDnsSettingService = $this->initMock(LocationDnsSettingService::class);
    }

    public function serviceMock()
    {
        $this->cdnService->shouldReceive('changeDefaultToTrue')->withAnyArgs()->andReturn(true);
        $this->locationDnsSettingService->shouldReceive('decideAction')->withAnyArgs()->andReturn(true);
        $this->locationDnsSettingService->shouldReceive('updateSetting')->withAnyArgs()->andReturn(true);
        $this->locationDnsSettingService->shouldReceive('createSetting')->withAnyArgs()->andReturn(true);
        $this->locationDnsSettingService->shouldReceive('destroy')->withAnyArgs()->andReturn(true);
        $this->locationDnsSettingService->shouldReceive('handelTargetDomainsIrouteSetting')->withAnyArgs()->andReturn(true);

    }

    public function testCreateDomainToGroup()
    {
        $request = new DomainGroupRequest;
        $request->merge(['domain_id' => 2,
                        'edited_by' => 'de20afd0-d009-4fbf-a3b0-2c3257915d10']);
        $response = $this->service->createDomainToGroup($request,$this->domainGroup);

        $this->assertNotEmpty($response);
    }
}
