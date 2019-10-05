<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hiero7\Models\DomainGroup;
use Hiero7\Services\{BatchGroupService,DomainGroupService};
use Hiero7\Repositories\DomainRepository;

class BatchGroupTest extends TestCase
{
    protected $batchGroupService;

    protected $domains = [];
    protected $user;

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
        $this->user = array("uuid" => \Illuminate\Support\Str::uuid(), "user_group_id" => 1);
        $this->domainGroup = DomainGroup::find(1);
        app()->call([$this, 'repository']);
        $this->batchGroupService = new BatchGroupService($this->domainGroupService, $this->domainRepository);
        app()->call([$this, 'serviceMock']);
    }

    protected function tearDown()
    {
        $this->user = null;
        $this->domains = [];
        $this->domainGroup = null;
        $this->batchGroupService = null;
        $this->domainGroupService = null;
        parent::tearDown();
    }

    public function repository(DomainGroupService $domainGroupService,DomainRepository $domainRepository)
    {
        $this->domainGroupService = $this->initMock(DomainGroupService::class);
        $this->domainRepository = $domainRepository;
    }

    public function serviceMock()
    {
        $this->domainGroupService->shouldReceive('changeCdnDefault')->withAnyArgs()->andReturn(true);
        $this->domainGroupService->shouldReceive('changeIrouteSetting')->withAnyArgs()->andReturn(true);
    }

    public function testStoreUnsuccess()
    {
        $this->domain = ['domains' => 
                            ['name' =>'hiero7.test1.com'],
                            ['name' =>'hiero7.test2.com'],
                            ['name' =>'rd.test1.com'],
                            ['name' =>'12345.com']
                        ];
        $result = $this->batchGroupService->store($this->domain,$this->domainGroup, $this->user);

        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('failure', $result);
        $this->assertEquals('Domain already exist at this Group.',$result['failure'][0]['message']);
        $this->assertEquals('The domain is undefined.',$result['failure'][2]['message']);
        $this->assertCount(4, $result['failure']);
    }
}
