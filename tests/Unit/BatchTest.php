<?php

namespace Tests\Unit;

use Hiero7\Services\BatchService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class BatchTest extends TestCase
{
    use DatabaseMigrations;
    protected $batchService;

    protected $domains = [];
    protected $user;

    protected function setUp()
    {
        parent::setUp();
        $this->domains[] = $this->addDomain("hello.com", $this->addCdn("cdn1", "cdn1.com", 90));
        $this->user = array("uuid" => \Illuminate\Support\Str::uuid(), "user_group_id" => 3);
        $this->batchService = $this->app->make('Hiero7\Services\BatchService');

    }

    public function tearDown()
    {
        $this->user = null;
        $this->domains = [];
        $this->batchService = null;
        parent::tearDown();
    }

    public function testDuplicateDomain() {
        $this->domains[] = $this->domains;
        $result = $this->batchService->store($this->domains, $this->user);
        $this->assertEquals(count($result), 1);
    }

    public function testDuplicateCdn() {
        $this->domains[0]["cdns"][] = $this->addCdn("cdn1", "cdn1.com");
        $result = $this->batchService->store($this->domains, $this->user);
        $this->assertEquals(count($result), 1);        
    }

    public function testBatchSuccess() {
        $this->domains[] = $this->addDomain("hello2.com", $this->addCdn("cdn1", "cdn1.com"), $this->addCdn("cdn2", "cdn2.com"));
        $result = $this->batchService->store($this->domains, $this->user);
        $this->assertEquals(count($result), 0);        
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