<?php

namespace Tests\Unit\Request;

use App\Http\Requests\DomainRequest;
use Hiero7\Models\Domain;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;

class DomainTest extends TestRequest
{
    use DatabaseMigrations, WithFaker;
    protected $request, $validator, $domain, $rules;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function setUp()
    {
        parent::setUp();
        $this->seed('DomainTableSeeder');
        $this->request = new FakeDomainRequest();
        $this->validator = $this->app['validator'];
        $this->domain = Domain::inRandomOrder()->first();
        $this->request->domain = $this->domain;
    }

    /** @test */
    public function domain_create_request()
    {
        $this->request->setName('domain.create');
        $this->rules = $this->request->rules();

        $this->assertTrue($this->validateField('user_group_id', null));
        $this->assertTrue($this->validateField('user_group_id', rand(1, 20)));

        $this->assertFalse($this->validateField('name', null));
        $this->assertFalse($this->validateField('name', $this->domain->name));
        $this->assertTrue($this->validateField('name', 'leo.com'));
        $this->assertFalse($this->validateField('name', str_random(10)));

        $this->assertTrue($this->validateField('cname', null));
        $this->assertTrue($this->validateField('cname', 'leo.com'));

        $this->assertTrue($this->validateField('label', null));
        $this->assertTrue($this->validateField('label', 'label'));
    }

    /** @test */
    public function domain_get_request()
    {
        $this->request->setName('domain.index');
        $this->rules = $this->request->rules();

        $this->assertTrue($this->validateField('user_group_id', null));
        $this->assertTrue($this->validateField('user_group_id', rand(1, 20)));
    }

    /** @test */
    public function domain_edit_request()
    {
        $this->request->setName('domain.edit');
        $this->rules = $this->request->rules();

        $this->assertTrue($this->validateField('name', null));
        $this->assertTrue($this->validateField('name', $this->domain->name));
        $this->assertTrue($this->validateField('name', 'leo.com'));
        $this->assertFalse($this->validateField('name', str_random(10)));

        // $this->assertTrue($this->validateField('cname', null));
        // $this->assertTrue($this->validateField('cname', $this->domain->cname));
        // $this->assertTrue($this->validateField('cname', 'leo.com'));

        $this->assertTrue($this->validateField('label', null));
        $this->assertTrue($this->validateField('label', 'label'));
    }

    /** @test */
    public function noRouteName()
    {
        $expected = [];
        $this->request->setName(null);
        $this->rules = $this->request->rules();
        $this->assertEquals($expected, $this->rules);
    }

    /** @test */
    public function authorize()
    {
        $this->assertTrue($this->request->authorize());
    }
}

class FakeDomainRequest extends DomainRequest
{
    public $name;

    public function setName($name)
    {
        $this->name = $name;
        return $name;
    }

    public function route($param = null, $default = null)
    {
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
}
