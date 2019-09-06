<?php

namespace Tests\Unit;

use Hiero7\Services\BatchService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Hiero7\Services\DnsProviderService;
use Faker\Factory as Faker;
use Hiero7\Models\Cdn;

class BatchTest extends TestCase
{
    use DatabaseMigrations;
    protected $batchService;
    protected $dnsprovider;

    protected $domains = [];
    protected $user;
    protected $cdn;

    protected function setUp()
    {
        parent::setUp();

        $this->dnsprovider = $this->initMock(DnsProviderService::class);
        if($this->getName() !== "testDnsPodError")
            $this->dnsprovider->shouldReceive('createRecord')
                ->withAnyArgs()
                ->andReturn(["errorCode"=>null,"data"=>["record"=>["id"=>1]]]);  

        $this->domains[] = $this->addDomain("hello.com", $this->addCdn("cdn1", "cdn1.com", 90));
        $this->user = array("uuid" => \Illuminate\Support\Str::uuid(), "user_group_id" => 3);
        $this->batchService = $this->app->make('Hiero7\Services\BatchService');
        $this->cdn = new Cdn();
    }

    public function tearDown()
    {
        $this->user = null;
        $this->domains = [];
        $this->batchService = null;
        $this->dnsprovider = null;
        parent::tearDown();
    }

    public function testDuplicateDomain() {
        $this->domains[] = $this->domains[0];
        $result = $this->batchService->store($this->domains, $this->user);
        $this->assertEquals(count($result), 1);
    }

    public function testAppendCdn(){
        $result = $this->batchService->store($this->domains, $this->user);
        // $this->assertEquals($result["hello.com"], []);

        $this->domains = [];
        $this->domains[] = $this->addDomain("hello.com", $this->addCdn("cdn10", "cdn10.com", 90));  
        $result = $this->batchService->store($this->domains, $this->user);

        // $this->assertEquals($result["hello.com"], []);
    }

    public function testDuplicateCdn() {
        $this->domains[0]["cdns"][] = $this->addCdn("cdn1", "cdn1.com");
        $result = $this->batchService->store($this->domains, $this->user);
        $this->assertEquals(count($result), 1);        
    }

    public function testBatchSuccess() {
        $this->domains[] = $this->addDomain("hello2.com", $this->addCdn("cdn1", "cdn1.com"), $this->addCdn("cdn2", "cdn2.com"));
        $result = $this->batchService->store($this->domains, $this->user);
        // $this->assertEquals($result["hello.com"], []);
        // $this->assertEquals($result["hello2.com"], []);
    }

    public function testBatchUpdate() {
        $actual = $this->cdn
            ->where("cname", $this->domains[0]["cdns"][0]["cname"])
            ->where("name", $this->domains[0]["cdns"][0]["name"])
            ->first();

        $this->assertEquals($actual, null);

        $result = $this->batchService->store($this->domains, $this->user);
        $actual = $this->cdn
            ->where("cname", $this->domains[0]["cdns"][0]["cname"])
            ->where("name", $this->domains[0]["cdns"][0]["name"])
            ->first();

        // $this->assertNotEquals($actual, null);

        $this->domains[0]["cdns"][0]["cname"] = "cdn999.com";
        $result = $this->batchService->store($this->domains, $this->user);
        $actual = $this->cdn
            ->where("cname", $this->domains[0]["cdns"][0]["cname"])
            ->where("name", $this->domains[0]["cdns"][0]["name"])
            ->first();

        // $this->assertNotEquals($actual, null);
    }

    public function testDnsPodError() {
        $this->dnsprovider = $this->dnsprovider->shouldReceive('createRecord')
            ->withAnyArgs()
            ->andReturn(["errorCode"=>4001,"message"=>"Subdomain roll record is limited : (500026)"]);   

        $result = $this->batchService->store($this->domains, $this->user);
        $this->assertEquals(count($result), 1);
    }

    protected function addDomain($name, array ...$cdn):array{
        return [
            'name' => $name,
            'cdns'  => $cdn,
        ];
    }  

    protected function addCdn($name, $cname, $ttl= 60):array{
        return [
            'name' => $name,
            'cname'  => $cname,
            'ttl' => $ttl
        ];
    }
}