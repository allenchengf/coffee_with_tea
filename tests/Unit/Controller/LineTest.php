<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/14
 * Time: 3:33 PM
 */

namespace Tests\Unit\Controller;

use App\Http\Controllers\Api\v1\LineController;
use App\Http\Requests\LineRequest as Request;
use Hiero7\Models\LocationNetwork as Line;
use Hiero7\Services\DnsProviderService;
use Hiero7\Services\LineService;
use Hiero7\Services\SchemeService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery as m;
use Tests\TestCase;

class LineTest extends TestCase
{
    use DatabaseMigrations;
    protected $lineService;
    protected $schemeService;
    protected $line;
    protected $jwtPayload = [];
    protected $spyDnsProviderService;

    protected function setUp()
    {
        parent::setUp();

        $this->seed();

        app()->call([$this, 'service']);

        $this->controller = new LineController($this->lineService, $this->schemeService);

        $this->line = new Line();

        $this->spyDnsProviderService = m::spy(DnsProviderService::class);
    }

    public function service(LineService $lineService, SchemeService $schemeService)
    {
        $this->lineService = $lineService;
        $this->schemeService = $schemeService;
    }

    /** @test */
    public function create_line()
    {
        $loginUid = 1;
        $request = new Request;

        $request->merge([
            'continent_id' => '1',
            'country_id' => '1',
            'location' => 'beijing',
            'network_id' => '5',
            'isp' => 'yidong',
        ]);

        $this->addUuidforPayload()->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->create($request);
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function create_exist_line()
    {
        $loginUid = 1;
        $errorCode = 4025;
        $request = new Request;

        $request->merge([
            'continent_id' => '1',
            'country_id' => '1',
            'location' => 'beijing',
            'network_id' => '5',
            'isp' => 'yidong',
        ]);

        $this->addUuidforPayload()
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $this->controller->create($request, $this->line);
        $response = $this->controller->create($request, $this->line);
        $this->assertEquals(400, $response->status());

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($errorCode, $data['errorCode']);
    }

    /** @test */
    public function edit_line()
    {
        $loginUid = 1;
        $request = new Request;

        $request->merge([
            'continent_id' => '1',
            'country_id' => '1',
            'location' => 'beijing',
            'network_id' => '5',
            'isp' => 'yidong',
        ]);

        $this->addUuidforPayload()
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $this->controller->create($request, $this->line);

        $line = $this->line->find(1);

        $editData = [
            'country_id' => 2,
            'continent_id' => 2,
            'isp' => 'dianxin',
            'location' => 'hebei',
        ];
        $request = new Request;
        $request->merge($editData);

        $response = $this->controller->edit($request, $line);
        $this->assertEquals(200, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($editData['country_id'], $data['data']['country_id']);
        $this->assertEquals($editData['continent_id'], $data['data']['continent_id']);
        $this->assertEquals($editData['isp'], $data['data']['isp']);
        $this->assertEquals($editData['location'], $data['data']['location']);
    }

    /** @test */
    public function delete_line()
    {
        $loginUid = 1;
        $request = new Request;

        $line = $this->line->find(1);

        $this->addUuidforPayload()
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $response = $this->controller->destroy($line, $this->spyDnsProviderService);

        $this->shouldUseSyncRecordToDnsPod();

        $this->assertEquals(200, $response->status());
    }

    private function shouldUseSyncRecordToDnsPod()
    {
        $this->spyDnsProviderService
            ->shouldHaveReceived('syncRecordToDnsPod')
            ->once();
    }

}
