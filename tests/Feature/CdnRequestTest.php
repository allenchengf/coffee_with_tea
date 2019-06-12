<?php

namespace Tests\Unit;

use App\Http\Middleware\AuthUserModule;
use App\Http\Middleware\DomainPermission;
use App\Http\Middleware\TokenCheck;
use Hiero7\Models\Cdn;
use Hiero7\Models\CdnProvider;
use Hiero7\Models\Domain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

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
        $this->seed('CdnProviderSeeder');

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
            'cname' => ["The cname field is required."],
        ])->assertStatus(422);

    }

    /**
     * @test
     * @group cdnRequest
     */
    public function createCdnButDomainValidationFails()
    {
        $this->addUuidforPayload()->setJwtTokenPayload(4, $this->jwtPayload);

        $requestParams = [
            'cname' => $this->faker->url,
        ];

        $this->post($this->uri,
            $requestParams)->assertStatus(422)->assertJsonFragment(['cname' => ["Domain Verification Error."]]);
    }

    /**
     * @test
     * @group cdnRequest
     */
    public function createCdnFailsWithNCdnProviderGroupNotMapping()
    {
        $cdnProvider = CdnProvider::inRandomOrder()->first();
        $domain = Domain::whereNotIn('user_group_id', [$cdnProvider->user_group_id])->inRandomOrder()->first();

        $requestParams = [
            'cdn_provider_id' => $cdnProvider->id,
            'cname' => $this->faker->domainName,
        ];

        $this->setUri($domain->id);

        $this->post($this->getUri(), $requestParams)
            ->assertStatus(422)
            ->assertJsonFragment(['cdn_provider_id' => ["The Domain And Cdn Provider User_group_id Not Mapping."]]);
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
            'cname' => $this->faker->url,
            'default' => false,
        ];

        $this->put($this->getUri() . "/$cdn->id",
            $requestParams)->assertStatus(422)->assertJsonFragment(['cname' => ["Domain Verification Error."]]);
    }

    /**
     * @test
     * @group cdnRequest
     */
    public function updateCdnButDomainAndCdnNoMapping()
    {
        $cdn = factory(Cdn::class)->create();

        $this->setUri($cdn->domain_id);

        $requestParams = [
            'cname' => $cdn->cname,
            'default' => false,
        ];

        $domain = Domain::whereNotIn('id', [$cdn->domain_id])->inRandomOrder()->first();

        $cdn2 = factory(Cdn::class)->create(['domain_id' => $domain->id]);

        $this->setUri($cdn->domain_id);

        $this->put($this->getUri() . "/$cdn2->id",
            $requestParams)->assertStatus(404);
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
            'name' => $this->faker->name,
            'cname' => $cdn->cname,
        ];

        $this->addUuidforPayload()->setJwtTokenPayload(4, $this->jwtPayload);

        $this->post($this->getUri(),
            $requestParams)->assertStatus(422)->assertJsonFragment(['cname' => ["The cname has already been taken."]]);
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
            'name' => $this->faker->name,
            'cname' => $this->faker->domainName,
            'default' => false,
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
