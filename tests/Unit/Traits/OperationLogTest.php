<?php

namespace Tests\Unit\Traits;

use Hiero7\Traits\OperationLogTrait;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OperationLogTest extends TestCase
{
    use OperationLogTrait;
    use DatabaseMigrations;

    /**
     * A basic unit test example.
     *
     * @return void
     */

    protected function setUp()
    {
        parent::setUp();
        $this->addUuidforPayload()
            ->addUserGroupId(1)
            ->setJwtTokenPayload(1);
        config(['app.env' => 'local']);

    }

    /**
     * @test
     */
    public function haveJWTTokenGetPayLoad()
    {
        $payload = $this->getJWTPayload();

        $this->assertIsArray($payload);
    }

    /**
     * @test
     */
    public function testSaveLog()
    {
        $category = 'Test Save Log';
        $this->setCategory($category);

        $this->setChangeFrom([
            'test' => 'test',
        ]);
        $this->setChangeTo([
            'test' => 'test2',
        ])->createOperationLog();

        $this->assertEquals($category, $this->category);
        $this->assertEquals('Check', $this->changeType);

    }

    /**
     * @test
     */
    public function testSaveLog_ChangeTypeIsUpdate()
    {
        $category = 'Test Save Log';
        $this->setCategory($category);

        $this->setChangeFrom([
            'test1' => 'test',
            'test2' => 'test',
        ]);

        $this->setChangeTo([
            'test1' => 'test2',
        ])->createOperationLog('', 'Update', 'SSS');

        $this->assertEquals($category, $this->category);

        $this->assertEquals([
            'test1' => 'test2',
        ], $this->getChangeTo());
    }

    protected function curlWithUri(string $domain, string $uri, array $body, string $method, $asJson = true)
    {
        return true;
    }

}
