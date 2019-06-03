<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use Hiero7\Models\Cdn;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CdnTest extends TestCase
{
    use DatabaseMigrations;
    protected $cdn;
    protected function setUp()
    {
        parent::setUp();
        $this->cdn = new Cdn();
    }

    public function tearDown()
    {
        parent::tearDown();
    }
    
    public function testUpdateOrInsertGetId()
    {
        $id = $this->cdn->updateOrInsertGetId([
            "domain_id"=>1,
            "name"=>"cdn1",
            "cname"=>"cdn1.com"
        ], []);
        $this->assertEquals($id, 1);

        $id = $this->cdn->updateOrInsertGetId([
            "domain_id"=>1,
            "name"=>"cdn2",
            "cname"=>"cdn2.com"
        ], []);
        $this->assertEquals($id, 2); 

        $id = $this->cdn->updateOrInsertGetId([
            "domain_id"=>1,
            "name"=>"cdn2",
        ], [
            "cname"=>"cdn22222.com"
        ]);
        $this->assertEquals($id, 2);                   
    }
}
