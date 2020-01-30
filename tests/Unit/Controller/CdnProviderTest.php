<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/6/14
 * Time: 3:53 PM
 */

namespace Tests\Unit\Controller;

use Mockery as m;
use Tests\TestCase;
use Hiero7\Models\Cdn;
use Hiero7\Models\Domain;
use Hiero7\Models\CdnProvider;
use Hiero7\Services\CdnService;
use Hiero7\Services\CdnProviderService;
use Hiero7\Services\DnsProviderService;
use Hiero7\Repositories\DomainRepository;
use Hiero7\Services\DnsPodRecordSyncService;
use Hiero7\Repositories\CdnProviderRepository;
use App\Http\Requests\CdnProviderRequest as Request;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Http\Controllers\Api\v1\CdnProviderController;

class CdnProviderTest extends TestCase
{
    use DatabaseMigrations;
    protected $cdnProviderService;
    protected $cdnProvider;
    protected $cdnProviderRepository;
    protected $domains = [];

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->dnsprovider = $this->initMock(DnsProviderService::class);

        app()->call([$this, 'repository']);
        app()->call([$this, 'service']);

        $this->mockDnsPodRecordSyncService = new DnsPodRecordSyncService($this->dnsprovider, $this->domainRepository);

        $this->controller = new CdnProviderController($this->cdnProviderService, $this->cdnService, $this->mockDnsPodRecordSyncService);
        $this->cdnProvider = new CdnProvider();
        $this->seed('CdnProviderSeeder');
    }


    public function repository(DomainRepository $domainRepository,CdnProviderRepository $cdnProviderRepository)
    {
        $this->domainRepository = $domainRepository;
        $this->cdnProviderRepository = $cdnProviderRepository;
    }

    public function service(CdnProviderService $cdnProviderService, CdnService $cdnService)
    {
        $this->cdnProviderService = $cdnProviderService;
        $this->cdnService = $cdnService;
    }

    /** @test */
    public function getCDNProviderByUgid()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $target_user_group_id = 1;
        $request = new Request;

        $request->merge([
            'user_group_id' => $target_user_group_id,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->index($request);
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function createCDNProvider()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $target_user_group_id = 1;
        $request = new Request;

        $request->merge([
            'user_group_id' => $target_user_group_id,
            'edited_by' => "de20afd0-d009-4fbf-a3b0-2c3257915d10",
            'status' => 'active',
            'name' => 'Cloudflare',
            'ttl' => 600,
            'url' => 'http://www.hiero7.com',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->store($request, $this->cdnProvider);
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function updateCDNProvider()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $target_user_group_id = 1;
        $request = new Request;

        $request->merge([
            'user_group_id' => $target_user_group_id,
            'edited_by' => "de20afd0-d009-4fbf-a3b0-2c3257915d10",
            'name' => 'Cloudflare',
            'ttl' => 600,
            'url' => 'http://www.hiero7.com',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $this->controller->store($request, $this->cdnProvider);

        $cdnProvider = $this->cdnProvider->find(1);

        $this->dnsprovider->shouldReceive('batchEditRecord')
            ->withAnyArgs()
            ->andReturn([
                "errorCode" => null, "data" => [
                    "job_id" => ["id" => 1],
                    "detail" => [
                        "domain_id" => 1,
                    ],
                ],
            ]);

        $editData = [
            'name' => 'Update',
            'ttl' => '666',
            'url' => 'http://www.google.com',
        ];
        $request = new Request;
        $request->merge($editData);

        $this->mockSyncRecordToDnsPod();

        $response = $this->controller->update($request, $cdnProvider);

        $this->dnsprovider->shouldReceive('createRecord')
            ->withAnyArgs()
            ->andReturn(["errorCode" => null, "data" => ["record" => ["id" => 1]]]);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->status());
        $this->assertEquals($editData['name'], $data['data']['name']);
        $this->assertEquals($editData['ttl'], $data['data']['ttl']);
    }

    /** @test */
    public function pauseCDNProvider()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $target_user_group_id = 1;
        $request = new Request;

        $request->merge([
            'user_group_id' => $target_user_group_id,
            'edited_by' => "de20afd0-d009-4fbf-a3b0-2c3257915d10",
            'name' => 'Cloudflare',
            'ttl' => 600,
            'url' => 'http://www.hiero7.com',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $this->controller->store($request, $this->cdnProvider);

        $cdnProvider = $this->cdnProvider->find(1);

        $this->dnsprovider->shouldReceive('batchEditRecord')
            ->withAnyArgs()
            ->andReturn([
                "errorCode" => null, "data" => [
                    "job_id" => ["id" => 1],
                    "detail" => [
                        "domain_id" => 1,
                    ],
                ],
            ]);

        $editData = [
            'status' => 'stop',
        ];
        $request = new Request;
        $request->merge($editData);
        $response = $this->controller->changeStatus($request, $cdnProvider);

        $this->dnsprovider->shouldReceive('batchEditRecord')
            ->withAnyArgs()
            ->andReturn(["errorCode" => null, "data" => ["record" => ["id" => 1]]]);

        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function resumeCDNProvider()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $target_user_group_id = 1;
        $request = new Request;

        $request->merge([
            'user_group_id' => $target_user_group_id,
            'edited_by' => "de20afd0-d009-4fbf-a3b0-2c3257915d10",
            'name' => 'Cloudflare',
            'ttl' => 600,
            'url' => 'http://www.hiero7.com',
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $this->controller->store($request, $this->cdnProvider);

        $cdnProvider = $this->cdnProvider->find(1);

        $this->dnsprovider->shouldReceive('batchEditRecord')
            ->withAnyArgs()
            ->andReturn([
                "errorCode" => null, "data" => [
                    "job_id" => ["id" => 1],
                    "detail" => [
                        "domain_id" => 1,
                    ],
                ],
            ]);

        $this->dnsprovider->shouldReceive('editRecord')->withAnyArgs()
            ->andReturn(["message" => "Success", "errorCode" => null, "data" => [
                "record" => [
                    "id" => "426278576",
                    "name" => "hiero7.test1.com",
                    "value" => "cCnPjg.com.",
                    "status" => "enable",
                    "weight" => null,
                ]]]);
        $editData = [
            'status' => 'active',
        ];

        $request = new Request;
        $request->merge($editData);
        $response = $this->controller->changeStatus($request, $cdnProvider);

        $this->dnsprovider->shouldReceive('batchEditRecord')
            ->withAnyArgs()
            ->andReturn(["errorCode" => null, "data" => ["record" => ["id" => 1]]]);

        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function activeAndStopScannableCDNProvider()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $target_user_group_id = 1;
        $request = new Request;

        $request->merge([
            'user_group_id' => $target_user_group_id,
            'edited_by' => "de20afd0-d009-4fbf-a3b0-2c3257915d10",
            'scannable' => 1,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->changeScannable($request, $this->cdnProvider->find(1)->first());

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->status());
        $this->assertEquals(true, $data['data']['scannable']);

        $request->merge([
            'user_group_id' => $target_user_group_id,
            'edited_by' => "de20afd0-d009-4fbf-a3b0-2c3257915d10",
            'scannable' => 0,
        ]);

        $response = $this->controller->changeScannable($request, $this->cdnProvider->find(1)->first());

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->status());
        $this->assertEquals(false, $data['data']['scannable']);
    }

    /** @test */
    public function errorNullUrlAtScannable()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $target_user_group_id = 1;
        $request = new Request;

        $request->merge([
            'user_group_id' => $target_user_group_id,
            'edited_by' => "de20afd0-d009-4fbf-a3b0-2c3257915d10",
            'scannable' => 1,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->changeScannable($request, $this->cdnProvider->find(5));

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals(4033, $data['errorCode']);
    }

    /** @test */
    public function errorStopStatusAtScannable()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $target_user_group_id = 1;
        $request = new Request;

        $request->merge([
            'user_group_id' => $target_user_group_id,
            'edited_by' => "de20afd0-d009-4fbf-a3b0-2c3257915d10",
            'scannable' => 1,
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->changeScannable($request, $this->cdnProvider->find(6));

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->status());
        $this->assertEquals(4032, $data['errorCode']);
    }

    /** @test */
    public function check_default_cdn()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $target_user_group_id = 1;

        $this->seed();
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');
        $this->createOnlyDefaultCdn();

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $cdnProvider = $this->cdnProvider->find(1);

        $response = $this->controller->checkDefault($cdnProvider);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->status());

        $this->assertEquals("hiero7.test1.com", $data['data']['have_multi_cdn'][0]);
        $this->assertEquals("hiero7.test2.com", $data['data']['have_multi_cdn'][1]);
        $this->assertEquals("only.default.com", $data['data']['only_default'][0]);
    }

    /** @test */
    public function delete_cdn_provider()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $this->seed();
        $request = new Request;

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $cdnProvider = $this->cdnProvider->find(1);

        $response = $this->controller->destroy($cdnProvider);
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function delete_cdn_provider_errorCodeIs3010()
    {
        $loginUid = 1;
        $user_group_id = 1;

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $cdnProvider = $this->cdnProvider->find(4);

        $response = $this->controller->destroy($cdnProvider);

        $this->assertEquals(403, $response->status());

        $data = json_decode($response->content(), true);

        $this->assertEquals(3010, $data['errorCode']);
    }

    /** @test */
    public function delete_cdn_provider_errorCodeIs4036()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $this->seed();
        $this->seed('DomainTableSeeder');
        $this->seed('CdnTableSeeder');

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $cdnProvider = $this->cdnProvider->find(1);

        $response = $this->controller->destroy($cdnProvider);

        $this->assertEquals(400, $response->status());

        $data = json_decode($response->content(), true);

        $this->assertEquals(4036, $data['errorCode']);
    }

    private function createOnlyDefaultCdn()
    {

        $domain = [
            'id' => 99,
            'user_group_id' => 1,
            'name' => 'only.default.com',
            'cname' => 'onlydefault.1',
        ];
        Domain::insert($domain);

        $cdn = [
            'domain_id' => 99,
            'cdn_provider_id' => 1,
            'cname' => str_random(6) . '.com',
            'default' => 1,
        ];

        Cdn::insert($cdn);
    }

    private function mockSyncRecordToDnsPod()
    {
        $this->dnsprovider
            ->shouldReceive('syncRecordToDnsPod')
            ->andReturn();
    }

}
