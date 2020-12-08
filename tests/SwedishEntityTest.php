<?php

namespace Olssonm\SwedishEntity\Tests;

use Illuminate\Support\Facades\Validator;
use Olssonm\SwedishEntity\Organization;
use Olssonm\SwedishEntity\Exceptions\DetectException;
use Olssonm\SwedishEntity\Entity;
use Olssonm\SwedishEntity\Person;

class SwedishEntityTest extends \Orchestra\Testbench\TestCase
{

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function testValidPeople()
    {
        $this->assertTrue((new Person('600411-8177'))->valid());
        $this->assertTrue((new Person('19860210-7313'))->valid());
        $this->assertTrue((new Person('600411+8177'))->valid());
        $this->assertTrue((new Person('19860210+7313'))->valid());
        $this->assertTrue((new Person('8905247188'))->valid());
        $this->assertTrue((new Person('196711202850'))->valid());
    }

    /** @test */
    public function testInvalidPeople()
    {
        $this->assertFalse((new Person('600412-8177'))->valid());
        $this->assertFalse((new Person('19860211-7313'))->valid());
        $this->assertFalse((new Person('8905257188'))->valid());
        $this->assertFalse((new Person('196711212850'))->valid());

        // Obviously false
		$this->assertFalse((new Person('00000000-0000'))->valid());
		$this->assertFalse((new Person('11111111-1111'))->valid());
		$this->assertFalse((new Person('22222222-2222'))->valid());
		$this->assertFalse((new Person('33333333-3333'))->valid());
		$this->assertFalse((new Person('44444444-4444'))->valid());
		$this->assertFalse((new Person('55555555-5555'))->valid());
		$this->assertFalse((new Person('66666666-6666'))->valid());
		$this->assertFalse((new Person('77777777-7777'))->valid());
		$this->assertFalse((new Person('88888888-8888'))->valid());
        $this->assertFalse((new Person('99999999-9999'))->valid());
    }
    
    /** @test */
    public function testPersonAttributes()
    {
        $person = new Person('600411-8177');
        $this->assertEquals(19, $person->century);
        $this->assertEquals(60, $person->year);
        $this->assertEquals(04, $person->month);
        $this->assertEquals(11, $person->day);
        $this->assertEquals(817, $person->num);
        $this->assertEquals(7, $person->check);
        $this->assertEquals(60, $person->age);
        $this->assertEquals('Apr-11', $person->birthday->format('M-d'));
        $this->assertEquals('male', $person->gender);
    }

    /** @test */
    public function testPeopleFormatting()
    {
        $this->assertEquals('196004118177', (new Person('600411-8177'))->format(12, false));
        $this->assertEquals('6004118177', (new Person('600411-8177'))->format(10, false));
        $this->assertEquals('19600411-8177', (new Person('600411-8177'))->format(12, true));
        $this->assertEquals('600411-8177', (new Person('600411-8177'))->format(10, true));

        $this->assertEquals('196004118177', (new Person('6004118177'))->format(12, false));
        $this->assertEquals('6004118177', (new Person('6004118177'))->format(10, false));
        $this->assertEquals('19600411-8177', (new Person('196004118177'))->format(12, true));
        $this->assertEquals('600411-8177', (new Person('196004118177'))->format(10, true));

        $this->assertEquals('200101012862', (new Person('010101-2862'))->format(12, false));
        $this->assertEquals('0101012862', (new Person('010101-2862'))->format(10, false));
        $this->assertEquals('20010101-2862', (new Person('20010101-2862'))->format(12, true));
        $this->assertEquals('010101-2862', (new Person('20010101-2862'))->format(10, true));
    }

    /** @test */
    public function testPeopleCentury()
    {
        $this->assertEquals('18600411+8177', (new Person('600411+8177'))->format(12));
        $this->assertEquals('20010101-6434', (new Person('010101-6434'))->format(12));
        $this->assertEquals('19010101+6434', (new Person('010101+6434'))->format(12));
        $this->assertEquals('190101016434', (new Person('010101+6434'))->format(12, false));
    }

    public function testPersonAsOrganization()
    {
        $this->assertFalse((new Organization('20010101-6434'))->valid());
        $this->assertFalse((new Organization('190101016434'))->valid());
    }

    /** @test */
    public function testValidCompanies()
    {
        $this->assertTrue((new Organization('556016-0680'))->valid());
        $this->assertTrue((new Organization('556103-4249'))->valid());
        $this->assertTrue((new Organization('5561034249'))->valid());
    }

    /** @test */
    public function testInvalidCompanies()
    {
        $this->assertFalse((new Organization('556016-0681'))->valid());
        $this->assertFalse((new Organization('556103-4250'))->valid());
        $this->assertFalse((new Organization('5561034250'))->valid());
    }

    /** @test */
    public function testOrganizationAttributes()
    {
        $organization1 = new Organization('556016-0680');
        $this->assertEquals(0, $organization1->check);
        $this->assertEquals('556016-0680', $organization1->org_no);
        $this->assertEquals('Aktiebolag', $organization1->type);

        $organization2 = new Organization('212000-1355');
        $this->assertEquals(5, $organization2->check);
        $this->assertEquals('212000-1355', $organization2->org_no);
        $this->assertEquals('Stat, landsting och kommuner', $organization2->type);
    }

    /** @test */
    public function testOrganizationFormatting()
    {
        $this->assertEquals('5560160680', (new Organization('556016-0680'))->format(false));
        $this->assertEquals('556016-0680', (new Organization('556016-0680'))->format(true));
        $this->assertEquals('556016-0680', (new Organization('5560160680'))->format(true));
    }

    /** @test */
    public function testSuccessfulDetect()
    {
        $this->assertEquals(Organization::class, get_class(Entity::detect('556016-0680')));
        $this->assertEquals(Organization::class, get_class(Entity::detect('5561034249')));
        $this->assertEquals(Organization::class, get_class(Entity::detect('212000-1355')));

        $this->assertEquals(Person::class, get_class(Entity::detect('600411-8177')));
        $this->assertEquals(Person::class, get_class(Entity::detect('19860210-7313')));
    }

    /** @test */
    public function testUnsuccessfulDetection()
    {
        $this->expectException(DetectException::class);
        Entity::detect('19212000-1355');
        Entity::detect('20212000-1355');
        Entity::detect('600411-8176');
    }

    /** @test */
    public function testLaravelValidator()
    {
        if (class_exists(Validator::class)) {
            $this->assertTrue($this->validate('556016-0680', 'organization'));
            $this->assertTrue($this->validate('5560160680', 'any'));
            $this->assertTrue($this->validate('556016-0680'));
            $this->assertFalse($this->validate('5560160680', 'person'));

            $this->assertTrue($this->validate('600411-8177', 'person'));
            $this->assertTrue($this->validate('6004118177', 'any'));
            $this->assertTrue($this->validate('19600411-8177', 'any'));
            $this->assertFalse($this->validate('600411-8177', 'organization'));

            $this->assertFalse($this->validate('aabbcc-ddee', 'any'));
            $this->assertFalse($this->validate('aabbccddee', 'organization'));
            $this->assertFalse($this->validate('00aabbccddee', 'person'));
        }
    }

    /**
     * Load the package
     *
     * @return array the packages
     */
    protected function getPackageProviders($app)
    {
        return [
            'Olssonm\SwedishEntity\SwedishEntityServiceProvider'
        ];
    }

    private function validate($number, $type = null)
    {
        $data = ['number' => $number];
        $validator = Validator::make($data, [
            'number' => sprintf('entity:%s', $type)
        ]);

        return $validator->passes();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}