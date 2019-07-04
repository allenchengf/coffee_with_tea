<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/6/14
 * Time: 3:53 PM
 */

namespace Tests\Unit\Controller;

use App\Http\Controllers\Api\v1\CdnProviderController;
use Hiero7\Models\CdnProvider;
use Hiero7\Repositories\CdnProviderRepository;
use Hiero7\Services\CdnProviderService;
use Hiero7\Services\DnsProviderService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Http\Requests\CdnProviderRequest as Request;
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
        app()->call([$this, 'service']);
        $this->controller = new CdnProviderController($this->cdnProviderService);
        $this->cdnProvider = new CdnProvider();
    }

    public function service(CdnProviderService $cdnProviderService)
    {
        $this->cdnProviderService = $cdnProviderService;
    }

    public function repository(CdnProviderRepository $cdnProviderRepository)
    {
        $this->cdnProviderRepository = $cdnProviderRepository;
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
            'ttl' => 600
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
            'ttl' => 600
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $this->controller->store($request, $this->cdnProvider);

        $cdnProvider = $this->cdnProvider->find(1);

        $this->dnsprovider->shouldReceive('batchEditRecord')
            ->withAnyArgs()
            ->andReturn([
                "errorCode"=>null,"data"=>[
                "job_id"=>["id"=>1],
                "detail"=>[
                    "domain_id"=>1
                ]
                    ]
                ]);

        $editData = [
            'name' => 'Update',
            'ttl' => '666'
        ];
        $request = new Request;
        $request->merge($editData);
        $response = $this->controller->update($request, $cdnProvider);

        $this->dnsprovider->shouldReceive('createRecord')
            ->withAnyArgs()
            ->andReturn(["errorCode"=>null,"data"=>["record"=>["id"=>1]]]);

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
            'ttl' => 600
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $this->controller->store($request, $this->cdnProvider);

        $cdnProvider = $this->cdnProvider->find(1);

        $this->dnsprovider->shouldReceive('batchEditRecord')
            ->withAnyArgs()
            ->andReturn([
                "errorCode"=>null,"data"=>[
                    "job_id"=>["id"=>1],
                    "detail"=>[
                        "domain_id"=>1
                    ]
                ]
            ]);

        $editData = [
            'status' => 'stop'
        ];
        $request = new Request;
        $request->merge($editData);
        $response = $this->controller->changeStatus($request, $cdnProvider);

        $this->dnsprovider->shouldReceive('batchEditRecord')
            ->withAnyArgs()
            ->andReturn(["errorCode"=>null,"data"=>["record"=>["id"=>1]]]);

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
            'ttl' => 600
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $this->controller->store($request, $this->cdnProvider);

        $cdnProvider = $this->cdnProvider->find(1);

        $this->dnsprovider->shouldReceive('batchEditRecord')
            ->withAnyArgs()
            ->andReturn([
                "errorCode"=>null,"data"=>[
                    "job_id"=>["id"=>1],
                    "detail"=>[
                        "domain_id"=>1
                    ]
                ]
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
            'status' => 'active'
        ];

        $request = new Request;
        $request->merge($editData);
        $response = $this->controller->changeStatus($request, $cdnProvider);

        $this->dnsprovider->shouldReceive('batchEditRecord')
            ->withAnyArgs()
            ->andReturn(["errorCode"=>null,"data"=>["record"=>["id"=>1]]]);

        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function check_default_cdn()
    {
        $loginUid = 1;
        $user_group_id = 1;
        $target_user_group_id = 1;
        $request = new Request;
        $this->seed();

        $request->merge([
            'user_group_id' => $target_user_group_id,
            'edited_by' => "de20afd0-d009-4fbf-a3b0-2c3257915d10",
            'name' => 'Cloudflare',
            'ttl' => 600
        ]);

        $this->addUuidforPayload()
            ->addUserGroupId($user_group_id)
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $this->controller->store($request, $this->cdnProvider);

        $cdnProvider = $this->cdnProvider->find(1);

        $response = $this->controller->checkDefault($request, $cdnProvider);
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(200, $response->status());
        $this->assertArrayHasKey('have_multi_cdn', $data['data']);
        $this->assertArrayHasKey('only_default', $data['data']);
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

        $this->dnsprovider->shouldReceive('editRecord')->withAnyArgs()
            ->andReturn(["message" => "Success", "errorCode" => null, "data" => [
                "record" => [
                    "id" => "426278576",
                    "name" => "hiero7.test1.com",
                    "value" => "cCnPjg.com.",
                    "status" => "enable",
                    "weight" => null,
                ]]]);

        $this->dnsprovider->shouldReceive('deleteRecord')
            ->withAnyArgs()
            ->andReturn([
                "errorCode"=>null,"data"=>[
                    "job_id"=>["id"=>1],
                    "detail"=>[
                        "domain_id"=>1
                    ]
                ]
            ]);
        $response = $this->controller->destroy($request, $cdnProvider);
        $this->assertEquals(200, $response->status());
    }
}
