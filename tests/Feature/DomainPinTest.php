<?php

namespace Tests\Feature;

use Hiero7\Models\DomainPin;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DomainPinTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();

        $this->seed();

        $this->uri = "/api/v1/domain-pin";

        $this->domainPin = DomainPin::inRandomOrder()->first();

        $this->addUserGroupId(1)->setJwtTokenPayload();
    }

    /**
     * @test
     */
    public function getAllDomainPin()
    {
        $expectedCount = DomainPin::count();

        $response = $this->call('GET', $this->uri);

        $response->assertStatus(200);

        $content = $response->getContent();

        $data = json_decode($content, true);

        $this->assertCount($expectedCount, $data['data']);
    }

    /**
     * @test
     */
    public function getDomainPinById()
    {
        $response = $this->call('GET', $this->uri . "/" . $this->domainPin->id);

        $response->assertStatus(200);

        $content = $response->getContent();

        $data = json_decode($content, true);

        $this->assertEquals($this->domainPin->name, $data['data']['name']);
    }

    /**
     * @test
     */
    public function createDomainPin()
    {
        $input = [
            'user_group_id' => 9999,
            'name' => str_random(10),
        ];

        $response = $this->call('POST', $this->uri, $input);

        $response->assertStatus(200);

        $content = $response->getContent();

        $data = json_decode($content, true);

        $this->assertEquals($input['name'], $data['data']['name']);
    }

    /**
     * @test
     */
    public function createDomainPinRequest_Error()
    {
        $input = [
            'user_group_id' => 1,
            'name' => str_random(10),
        ];

        $this->json('POST', $this->uri, $input)->assertJsonFragment(
            ["The user group id has already been taken."]
        )->assertStatus(422);
    }


    /**
     * @test
     */
    public function deleteDomainPin()
    {
        $response = $this->call('DELETE', $this->uri . "/" . $this->domainPin->id);

        $response->assertStatus(200);

        $content = $response->getContent();

        $data = json_decode($content, true);

        $this->assertEquals($this->domainPin->name, $data['data']);
    }
}
