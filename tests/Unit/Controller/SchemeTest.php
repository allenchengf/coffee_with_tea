<?php
/**
 * Created by PhpStorm.
 * User: allen
 * Date: 2019/5/16
 * Time: 4:45 PM
 */

namespace Tests\Unit\Controller;

use App\Http\Controllers\Api\v1\SchemeController;
use Hiero7\Models\Scheme;
use Hiero7\Services\SchemeService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Http\Requests\SchemeRequest as Request;
class SchemeTest extends TestCase
{
    use DatabaseMigrations;
    protected $SchemeService;
    protected $scheme;
    protected $jwtPayload = [];

    protected function setUp()
    {
        parent::setUp();
        $this->seed();
        app()->call([$this, 'service']);
        $this->controller = new SchemeController($this->SchemeService);
        $this->scheme = new Scheme();
    }

    public function service(SchemeService $schemeService)
    {
        $this->SchemeService = $schemeService;
    }

    /** @test */
    public function create_scheme()
    {
        $loginUid = 1;
        $request = new Request;

        $request->merge([
            'name' => 'xxx',
        ]);

        $this->addUuidforPayload()->setJwtTokenPayload($loginUid, $this->jwtPayload);;

        $response = $this->controller->create($request, $this->scheme);
        $this->assertEquals(200, $response->status());
    }

    /** @test */
    public function edit_scheme()
    {
        $loginUid = 1;
        $request = new Request;

        $request->merge([
            'name' => 'xxx',
        ]);

        $this->addUuidforPayload()
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $this->controller->create($request, $this->scheme);

        $line = $this->scheme->find(1);

        $editData = [
            'name' => 'yyy',
        ];
        $request = new Request;
        $request->merge($editData);


        $response = $this->controller->edit($request, $line);
        $this->assertEquals(200, $response->status());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($editData['name'], $data['data']['name']);
    }

    /** @test */
    public function delete_scheme()
    {
        $loginUid = 1;
        $request = new Request;

        $request->merge([
            'name' => 'xxx',
        ]);

        $this->addUuidforPayload()
            ->setJwtTokenPayload($loginUid, $this->jwtPayload);

        $this->controller->create($request, $this->scheme);

        $scheme = $this->scheme->find(1);

        $response = $this->controller->destroy($scheme);
        $this->assertEquals(200, $response->status());
    }
}
