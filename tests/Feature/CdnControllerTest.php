<?php

namespace Tests\Feature;

use App\Events\CdnWasBatchEdited;
use App\Events\CdnWasCreated;
use App\Events\CdnWasDelete;
use App\Events\CdnWasEdited;
use App\Http\Middleware\AuthUserModule;
use App\Http\Middleware\DomainPermission;
use App\Http\Middleware\TokenCheck;
use App\Http\Middleware\CheckDnsPod;
use Hiero7\Models\Cdn;
use Hiero7\Models\CdnProvider;
use Hiero7\Models\Domain;
use Hiero7\Models\LocationDnsSetting;
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

    protected $mockService, $domain, $cdn, $controller, $uri, $cdnProvider, $defaultCdn;

    protected function setUp()
    {
        parent::setUp();

        $this->seed('DomainTableSeeder');
        $this->seed('CdnProviderSeeder');
        $this->seed('SchemeTableSeeder');
        $this->seed('ContinentTableSeeder');
        $this->seed('CountryTableSeeder');
        $this->seed('NetworkTableSeeder');
        $this->seed('LocationNetworkTableSeeder');

        $this->login();

        $this->withoutMiddleware([AuthUserModule::class, TokenCheck::class, DomainPermission::class, CheckDnsPod::class]);

        $this->domain = Domain::where('user_group_id', 1)->inRandomOrder()->first();

        $this->cdn = Cdn::inRandomOrder()->first();

        $this->cdnProvider = CdnProvider::where('user_group_id', 1)->inRandomOrder()->first();

        $this->uri = "/api/v1/domains/{$this->domain->id}/cdn";

    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    private function login()
    {
        $this->addUuidforPayload()->addUserGroupId(1)->addRoleIdforPayload(1)->setJwtTokenPayload(1,
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

        $this->setDaultCdn();

        $this->setUri($this->defaultCdn->domain_id);

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

        $this->setDaultCdn();

        $cdn = factory(Cdn::class)->create([
            'cdn_provider_id' => $this->cdnProvider->id,
            'domain_id' => $this->defaultCdn->domain_id,
            'default' => false,
        ]);

        $this->setUri($cdn->domain_id);

        $this->patch($this->getUri() . "/$cdn->id/default", ['default' => true])
            ->assertStatus(409);

        Event::assertDispatched(CdnWasEdited::class);
    }

    /**
     * @test
     * @group cdn
     */
    public function changeDefaultCdn()
    {
        Event::fake([CdnWasEdited::class]);

        $this->setDaultCdn();

        $cdn = factory(Cdn::class)->create([
            'domain_id' => $this->domain->id,
            'cdn_provider_id' => $this->cdnProvider->id,
            'default' => false,
        ]);

        $this->setUri($cdn->domain_id);

        $this->patch($this->getUri() . "/$cdn->id/default", ['default' => 1])
            ->assertStatus(409); 

        Event::assertDispatched(CdnWasEdited::class);
    }

    /**
     * @test
     * @group cdn
     */
    public function changeCnameNotEventDispatched()
    {
        Event::fake([CdnWasEdited::class]);
        Event::fake([CdnWasBatchEdited::class]);

        $this->setDaultCdn();

        $cdn = factory(Cdn::class)->create([
            'domain_id' => $this->domain->id,
            'cdn_provider_id' => $this->cdnProvider->id,
            'default' => false,
        ]);

        $this->setUri($cdn->domain_id);

        $this->patch($this->getUri() . "/$cdn->id/cname", $this->getRequestBody())
            ->assertStatus(200);
        // Event::assertDispatched(CdnWasEdited::class);
        Event::assertDispatched(CdnWasBatchEdited::class);
    }

    /**
     * @test
     * @group cdn
     */
    public function changeCnameAndEventDispatched()
    {
        Event::fake([CdnWasEdited::class]);
        // Event::fake([CdnWasBatchEdited::class]);

        $this->setDaultCdn();

        $this->patch($this->getUri() . "/" . $this->defaultCdn->id . "/cname", $this->getRequestBody())
            ->assertStatus(409);

        Event::assertDispatched(CdnWasEdited::class);
        // Event::assertDispatched(CdnWasBatchEdited::class);
    }

    /**
     * @test
     * @group cdn
     */
    public function destroyCdn()
    {
        Event::fake([CdnWasDelete::class]);

        $this->setDaultCdn();

        $cdn = factory(Cdn::class)->create([
            'domain_id' => $this->defaultCdn->domain_id,
            'default' => false,
            'cdn_provider_id' => $this->cdnProvider->id,
        ]);

        factory(LocationDnsSetting::class)->create([
            'cdn_id' => $cdn->id
        ]);

        $this->setUri($cdn->domain_id);
        $this->delete($this->getUri() . "/$cdn->id")
            ->assertStatus(200);

        Event::assertDispatched(CdnWasDelete::class);
    }

    private function setDaultCdn()
    {
        $this->defaultCdn = factory(Cdn::class)->create([
            'domain_id' => $this->domain->id,
            'default' => true,
        ]);

        $this->cdnProvider = CdnProvider::where('user_group_id', 1)
            ->whereNotIn('id', [$this->defaultCdn->cdn_provider_id])
            ->inRandomOrder()
            ->first();
    }

    private function getRequestBody()
    {
        return [
            'cdn_provider_id' => $this->cdnProvider->id,
            'cname' => $this->faker->domainName,
        ];
    }

}
