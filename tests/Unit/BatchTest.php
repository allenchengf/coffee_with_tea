<?php

namespace Tests\Unit;

use Hiero7\Services\BatchService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Hiero7\Services\DnsProviderService;
use Faker\Factory as Faker;
use Hiero7\Models\Cdn;
use Hiero7\Repositories\{CdnRepository, DomainRepository, CdnProviderRepository};


class BatchTest extends TestCase
{
    use DatabaseMigrations;
    protected $batchService;
    protected $dnsProvider;
    protected $cdnRepository;
    protected $cdnProviderRepository;

    protected $domains = [];
    protected $user;
    protected $cdn;

    protected function setUp()
    {
        parent::setUp();
        
        app()->call([$this, 'repository']);
        app()->call([$this, 'mockery']);
        $this->seed();
        $this->seed('DomainTableSeeder');
        $this->batchService = new BatchService($this->cdnRepository,$this->dnsProvider, $this->domainRepository, $this->cdnProviderRepository);

        $this->user = array("uuid" => \Illuminate\Support\Str::uuid(), "user_group_id" => 1);
        $this->cdn = new Cdn();
    }

    public function repository(CdnRepository $cdnRepository,
        DomainRepository $domainRepository,
        CdnProviderRepository $cdnProviderRepository)
    {
        $this->cdnRepository = $cdnRepository;
        $this->dnsProvider = $this->initMock(DnsProviderService::class);
        $this->domainRepository = $domainRepository;
        $this->cdnProviderRepository = $cdnProviderRepository;
    }

    public function mockery()
    {
        $this->dnsProvider->shouldReceive('createRecord')
        ->withAnyArgs()
        ->andReturn(["errorCode"=>4001,"message"=>"Subdomain roll record is limited : (500026)"]);  

    }

    public function tearDown()
    {
        $this->user = null;
        $this->domains = [];
        $this->batchService = null;
        $this->dnsProvider = null;
        parent::tearDown();
    }

    public function testBatchAddDomainNonCdnToAdd() 
    {
        $this->domains[] = $this->addDomain("hello2.com");

        $result = $this->batchService->store($this->domains, $this->user);

        $this->assertEquals(count($result), 2);

        $this->assertArrayHasKey('success', $result);
        $this->assertEquals($result['success']['domain'], []); 

        $this->assertArrayHasKey('failure', $result);
        $this->assertEquals($result['failure']['domain'][0]['name'], 'hello2.com');        
        $this->assertEquals($result['failure']['domain'][0]['cdn'], []); 
    }

    public function testBatchAddDomainSuccessAndAddCdnFail()
    {
        $this->domains[] = $this->addDomain("hello.com", $this->addCdn("Hiero7", "hiero8.hero.com"));

        $result = $this->batchService->store($this->domains, $this->user);

        $this->assertEquals(count($result), 2);

        $this->assertArrayHasKey('success', $result);
        $this->assertEquals($result['success']['domain'][0]['name'], 'hello.com'); 
        $this->assertArrayHasKey('cdn', $result['success']['domain'][0]);
        $this->assertEquals($result['success']['domain'][0]['cdn'], []); 

        $this->assertArrayHasKey('failure', $result);
        $this->assertEquals($result['failure']['domain'][0]['name'], 'hello.com');        
        $this->assertEquals($result['failure']['domain'][0]['cdn'][0]['name'], 'Hiero7'); 

        $this->assertArrayHasKey('errorCode', $result['failure']['domain'][0]['cdn'][0]);
        $this->assertArrayHasKey('message', $result['failure']['domain'][0]['cdn'][0]);
    }

    protected function addDomain($name, array ...$cdn):array{
        return [
            'name' => $name,
            'cdns'  => $cdn,
        ];
    }  

    protected function addCdn($name, $cname):array{
        return [
            'name' => $name,
            'cname'  => $cname
        ];
    }
}