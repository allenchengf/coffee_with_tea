<?php

namespace Tests\Unit;

use App\Http\Middleware\AuthUserModule;
use App\Http\Middleware\DomainPermission;
use App\Http\Middleware\TokenCheck;
use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;


class CdnRequestTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected $mockService;

    protected $domain, $cdn;

    protected $uri;

    protected function setUp()
    {
        parent::setUp();

        $this->withoutMiddleware([AuthUserModule::class, TokenCheck::class, DomainPermission::class]);

        $this->seed('DomainTableSeeder');

        $this->domain = Domain::inRandomOrder()->first();

        $this->uri = "/api/v1/domains/{$this->domain->id}/cdn";

    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    private function setUri($domainId)
    {
        $this->uri = "/api/v1/domains/{$domainId}/cdn";
    }

    private function getUri()
    {
        return $this->uri;
    }

    /**
     * @test
     * @group cdnRequest
     */
    public function createCdnFailsWithNoAttribute()
    {
        $this->json('POST', $this->uri)->assertJsonFragment([
            'name'  => ["The name field is required."],
            'cname' => ["The cname field is required."]
        ])->assertStatus(422);

    }

    /**
     * @test
     * @group cdnRequest
     */
    public function createCdnNameIsTakenWithSameDomainId()
    {
        $cdn = factory(Cdn::class)->create();

        $this->setUri($cdn->domain_id);

        $requestParams = [
            'name'  => $cdn->name,
            'cname' => $this->faker->url,
        ];

        $this->addUuidforPayload()->setJwtTokenPayload(4, $this->jwtPayload);

        $this->post($this->getUri(),
            $requestParams)->assertStatus(422)->assertJsonFragment(['name' => ["The name has already been taken."],]);

    }

    /**
     * @test
     * @group cdnRequest
     */
    public function createCdnButDomainValidationFails()
    {
        $this->addUuidforPayload()->setJwtTokenPayload(4, $this->jwtPayload);

        $requestParams = [
            'name'  => $this->faker->name,
            'cname' => $this->faker->url,
        ];

        $this->post($this->uri,
            $requestParams)->assertStatus(422)->assertJsonFragment(['cname' => ["Domain Verification Error."],]);
    }

    /**
     * @test
     * @group cdnRequest
     */
    public function updateCdnButDomainValidationFails()
    {
        $cdn = factory(Cdn::class)->create();

        $this->setUri($cdn->domain_id);

        $requestParams = [
            'name'    => $this->faker->name,
            'cname'   => $this->faker->url,
            'default' => false
        ];

        $this->put($this->getUri() . "/$cdn->id",
            $requestParams)->assertStatus(422)->assertJsonFragment(['cname' => ["Domain Verification Error."],]);;
    }

    /**
     * @test
     * @group cdnRequest
     */
    public function createCdnCnameIsTakenWithSameDomainId()
    {
        $cdn = factory(Cdn::class)->create();

        $this->setUri($cdn->domain_id);

        $requestParams = [
            'name'  => $this->faker->name,
            'cname' => $cdn->cname,
        ];

        $this->addUuidforPayload()->setJwtTokenPayload(4, $this->jwtPayload);

        $this->post($this->getUri(),
            $requestParams)->assertStatus(422)->assertJsonFragment(['cname' => ["The cname has already been taken."],]);
    }

    /**
     * @test
     * @group cdnRequest
     * @throws \Exception
     */
    public function createCdnFailsWhenTtlIsLessThan600()
    {
        $cdn = factory(Cdn::class)->create();

        $this->setUri($cdn->domain_id);

        $requestParams = [
            'name'  => $cdn->name,
            'cname' => $this->faker->url,
            'ttl'   => random_int(1, 500)
        ];

        $this->addUuidforPayload()->setJwtTokenPayload(4, $this->jwtPayload);

        $this->post($this->getUri(),
            $requestParams)->assertStatus(422)->assertJsonFragment(['ttl' => ["The ttl must be at least 600."],]);
    }


    /**
     * @test
     * @group cdnRequest
     * @throws \Exception
     */
    public function createCdnFailsWhenTtlIsLessThan604800()
    {
        $cdn = factory(Cdn::class)->create();

        $this->setUri($cdn->domain_id);

        $requestParams = [
            'name'  => $cdn->name,
            'cname' => $this->faker->url,
            'ttl'   => random_int(604801, 800000)
        ];

        $this->addUuidforPayload()->setJwtTokenPayload(4, $this->jwtPayload);

        $this->post($this->getUri(),
            $requestParams)->assertStatus(422)->assertJsonFragment(['ttl' => ["The ttl may not be greater than 604800."],]);
    }

    /**
     * @test
     * @group cdnRequest
     */
    public function updateDefaultCdnToNonDefaultFails()
    {
        $cdn = factory(Cdn::class)->create(['default' => true]);

        $this->setUri($cdn->domain_id);

        $requestParams = [
            'name'    => $this->faker->name,
            'cname'   => $this->faker->domainName,
            'default' => false
        ];

        $this->put($this->getUri() . "/$cdn->id", $requestParams)->assertStatus(403);
    }

    /**
     * @test
     * @group cdnRequest
     */
    public function deleteCdnFailsWhenDefaultIsTrue()
    {
        $cdn = factory(Cdn::class)->create(['default' => true]);

        $this->setUri($cdn->domain_id);

        $this->delete($this->getUri() . "/$cdn->id", [])->assertStatus(403);
    }


    /**
     * @test
     * @group cdnRequest
     */
    public function deleteCdnSuccessWhenDefaultIsFalse()
    {
        $cdn = factory(Cdn::class)->create(['default' => false]);

        $this->setUri($cdn->domain_id);

        $this->delete($this->getUri() . "/$cdn->id", [])->assertStatus(200);
    }
}
