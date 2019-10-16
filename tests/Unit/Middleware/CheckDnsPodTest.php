<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use App\Http\Middleware\CheckDnsPod;
use Hiero7\Services\DnsProviderService;
use Illuminate\Http\Request;



class CheckDnsPodTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->dnsProviderService = $this->initMock(DnsProviderService::class);
        $this->middleware = new CheckDnsPod($this->dnsProviderService);
        app()->call([$this, 'mockErrorReturn']);
    }

    public function mockErrorReturn()
    {
        $result = new \stdClass();
        $result->content = [ "message" => "DNS Pod response error ( Http Code : 301) : (5001)",
                            "errorCode" => 5001,
                            "data" => []
                            ];
        $result->status = 400;
        $result->contentType = "application/json";

        $this->dnsProviderService->shouldReceive('getDomain')->withAnyArgs()->andReturn($result);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testDnsPodNotWrk()
    {
        $request = new Request;

        $response = $this->middleware->handle($request, function () {});

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($response->status(),503);
        $this->assertEquals($data['message'], "Please Contact IRoute Admin.");
        $this->assertEquals($data['errorCode'], 5005);

    }
}
