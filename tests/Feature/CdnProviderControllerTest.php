<?php

namespace Tests\Feature;

use Hiero7\Models\CdnProvider;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Api\v1\LocationDnsSettingController;
use App\Http\Middleware\AuthUserModule;
use App\Http\Middleware\DomainPermission;
use App\Http\Middleware\TokenCheck;
use App\Http\Middleware\CheckDnsPod;

class CdnProviderControllerTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->withoutMiddleware([
            AuthUserModule::class,
            TokenCheck::class,
            DomainPermission::class,
        ]);

        $this->artisan('migrate');

        $this->seed();

        $this->uri              = "/api/v1/cdn_providers";

        $this->loginUserGroupId = 1;

        $this->uid              = 1;

        $this->login();
    }

    /**
     * @test
     * @group dashboard
     */
    public function listAllTheCDNProviderAndTheStatusAndDomainQuantity()
    {
        $response = $this->call('GET', $this->uri . '/detailed-info');

        $data = json_decode($response->getContent(), true);

        $keysToBeAsserted = ['name', 'status', 'default_domains_count'];

        if (count($data['data']) > 0) {

            for ($i = 0; $i < count($keysToBeAsserted); $i++) {

                $this->assertArrayHasKey($keysToBeAsserted[$i], $data['data'][0]);
            }
        }

        $response->assertStatus(200);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    private function login()
    {
        $this->addUuidforPayload()->addUserGroupId($this->loginUserGroupId)->setJwtTokenPayload($this->uid,
            $this->jwtPayload);
    }

    /**
     * @test
     * @group dashboard
     */
    public function specificCdnProviderOnlyCanBeSeenBySpecificGroup()
    {
        $cdnProvidersActualCount = CdnProvider::where('user_group_id', $this->jwtPayload['user_group_id'])->count();

        $response = $this->call('GET', $this->uri . '/detailed-info');

        $data = json_decode($response->getContent(), true);

        $response->assertStatus(200);

        $this->assertEquals($cdnProvidersActualCount, count($data['data']));

    }
}
