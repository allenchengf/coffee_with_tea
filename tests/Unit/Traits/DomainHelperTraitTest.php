<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use Hiero7\Traits\DomainHelperTrait;

class DomainHelperTraitTest extends TestCase
{
    use DomainHelperTrait;

    protected function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }
    public function testValidateDoamin()
    {
        $correctType = [
            'domain.com',
            'example.domain.com',
            'example.domain-hyphen.com',
            'www.domain.com',
            'example.museum',
            'www.domain.123.456.789.000.com'
        ];
        foreach ($correctType as $v) {
            $this->assertEquals(true, $this->validateDomain($v));
        }
        

        $errorType = [
            'http://example.com',
            'subdomain.-example.com',
            'example.com/parameter',
            'example.com?anything'
        ];
        foreach ($errorType as $v) {
            $this->assertEquals(false, $this->validateDomain($v));
        }
    }
}
