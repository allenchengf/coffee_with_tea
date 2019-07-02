<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Hiero7\Models\DomainGroup;

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
        $this->domainGroupService = $this->initMock(CdnService::class);
        app()->call([$this, 'serviceMock']);
        $this->user = array("uuid" => \Illuminate\Support\Str::uuid(), "user_group_id" => 1);
        $this->domainGroup = DomainGroup::find(1);
        $this->batchGroupService =  $this->app->make('Hiero7\Services\BatchGroupService');
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
        $this->assertArrayHasKey('12345.com', $result);
        $this->assertEquals('Domain already exist at this Group.',$result['hiero7.test1.com']);
        $this->assertEquals('The domain is undefined.',$result['rd.test1.com']);
        $this->assertCount(4, $result);
    }
}
