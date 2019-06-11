<?php

namespace Tests\Feature;

use App\Events\CdnWasCreated;
use App\Events\CdnWasEdited;
use App\Http\Middleware\AuthUserModule;
use App\Http\Middleware\DomainPermission;
use App\Http\Middleware\TokenCheck;
use Hiero7\Models\Cdn;
use Hiero7\Models\CdnProvider;
use Hiero7\Models\Domain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Class CdnControllerTest
 * @package Tests\Feature
 */
class CdnControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected $mockService, $domain, $cdn, $controller, $uri, $cdnProvider;

    protected function setUp()
    {
        parent::setUp();

        $this->seed('DomainTableSeeder');
        $this->seed('CdnProviderSeeder');

        $this->login();

        $this->withoutMiddleware([AuthUserModule::class, TokenCheck::class, DomainPermission::class]);

        $this->domain = Domain::where('user_group_id', 1)->inRandomOrder()->first();

        $this->cdn = Cdn::inRandomOrder()->first();

        $this->cdnProvider = CdnProvider::inRandomOrder()->first();

        $this->uri = "/api/v1/domains/{$this->domain->id}/cdn";

    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    private function login()
    {
        $this->addUuidforPayload()->addUserGroupId(random_int(1, 5))->setJwtTokenPayload(random_int(1, 5),
            $this->jwtPayload);
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
     * @group cdn
     */
    public function testIndex()
    {
        $response = $this->call('GET', $this->uri);

        $response->assertStatus(200);

    }

    /**
     * @test
     * @group cdn
     */
    public function testCreateCdn()
    {
        Event::fake([CdnWasCreated::class]);

        $this->call('POST', $this->uri, $this->getRequestBody());

        Event::assertDispatched(CdnWasCreated::class);

    }

    /**
     * @test
     * @group cdn
     *
     */
    public function createCdnEventNotDispatched()
    {
        Event::fake([CdnWasCreated::class]);

        $cdn = factory(Cdn::class)->create(['default' => true]);

        $this->cdnProvider = CdnProvider::whereNotIn('id', [$cdn->cdn_provider_id])->inRandomOrder()->first();

        $this->setUri($cdn->domain_id);

        $this->post($this->getUri(), $this->getRequestBody())->assertStatus(200);

        Event::assertNotDispatched(CdnWasCreated::class);
    }

    /**
     * @test
     * @group cdn
     */
    public function editDefaultCdnAndEventDispatched()
    {
        Event::fake([CdnWasEdited::class]);

        $defaultCdn = factory(Cdn::class)->create(['default' => true]);

        $this->cdnProvider = CdnProvider::whereNotIn('id', [$defaultCdn->cdn_provider_id])->inRandomOrder()->first();

        $cdn = factory(Cdn::class)->create([
            'cdn_provider_id' => $this->cdnProvider->id,
            'domain_id' => $defaultCdn->domain_id,
            'default' => false,
        ]);

        $this->setUri($cdn->domain_id);

        $this->put($this->getUri() . "/$cdn->id",
            array_merge($this->getRequestBody(), ['default' => true]))
            ->assertStatus(500);
        Event::assertDispatched(CdnWasEdited::class);
    }

    /**
     * @test
     * @group cdn
     */
    public function editCdnEventNotDispatched()
    {

        Event::fake([CdnWasEdited::class]);

        $defaultCdn = factory(Cdn::class)->create([
            'domain_id' => $this->domain->id,
            'default' => true,
        ]);

        $this->cdnProvider = CdnProvider::whereNotIn('id', [$defaultCdn->cdn_provider_id])->inRandomOrder()->first();

        $cdn = factory(Cdn::class)->create([
            'domain_id' => $this->domain->id,
            'cdn_provider_id' => $this->cdnProvider->id,
            'default' => false,
        ]);

        $this->setUri($cdn->domain_id);

        $this->put($this->getUri() . "/$cdn->id",
            array_merge($this->getRequestBody(), ['default' => 0]))
            ->assertStatus(200);

        Event::assertNotDispatched(CdnWasEdited::class);
    }

    /**
     * @test
     * @group cdn
     */
    public function changeDefaultCdn()
    {
        Event::fake([CdnWasEdited::class]);

        $defaultCdn = factory(Cdn::class)->create([
            'domain_id' => $this->domain->id,
            'default' => true,
        ]);

        $this->cdnProvider = CdnProvider::whereNotIn('id', [$defaultCdn->cdn_provider_id])->inRandomOrder()->first();

        $cdn = factory(Cdn::class)->create([
            'domain_id' => $this->domain->id,
            'cdn_provider_id' => $this->cdnProvider->id,
            'default' => false,
        ]);

        $this->setUri($cdn->domain_id);

        $this->put($this->getUri() . "/$cdn->id",
            array_merge($this->getRequestBody(), ['default' => 1]))->assertStatus(500);

        Event::assertDispatched(CdnWasEdited::class);
    }

    /**
     * @test
     * @group cdn
     */
    public function testDestroyCdn()
    {
        $cdn = factory(Cdn::class)->create(['default' => false]);

        $this->setUri($cdn->domain_id);

        $this->delete($this->getUri() . "/$cdn->id", [])->assertStatus(200);

    }

    private function getRequestBody()
    {
        return [
            'cdn_provider_id' => $this->cdnProvider->id,
            'cname' => $this->faker->domainName,
        ];
    }

}
