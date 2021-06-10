<?php

namespace Olssonm\SwedishEntity\Tests;

use Carbon\Carbon;
use ErrorException;
use Illuminate\Support\Facades\Validator;
use Olssonm\SwedishEntity\Organization;
use Olssonm\SwedishEntity\Entity;
use Olssonm\SwedishEntity\Person;
use Olssonm\SwedishEntity\Exceptions\DetectException;
use Olssonm\SwedishEntity\Exceptions\OrganizationException;
use Olssonm\SwedishEntity\Exceptions\PersonException;
use Olssonm\SwedishEntity\Helpers\Cleaner;
use Personnummer\Personnummer;

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

    public function testPersonnummerInstance()
    {
        $person = new Person('600411-8177');
        $this->assertEquals('Personnummer\Personnummer', get_class($person->getPersonnummerInstance()));
    }

    /** @test */
    public function testPersonAttributes()
    {
        $person1 = new Person('600411-8177');
        $this->assertEquals('600411-8177', $person1->ssn);
        $this->assertEquals(19, $person1->century);
        $this->assertEquals(60, $person1->year);
        $this->assertEquals(04, $person1->month);
        $this->assertEquals(11, $person1->day);
        $this->assertEquals(817, $person1->num);
        $this->assertEquals(7, $person1->check);
        $this->assertEquals(Carbon::now()->setYear('1960')->diffInYears(Carbon::now()), $person1->age);
        $this->assertEquals('Personnummer', $person1->type);
        $this->assertEquals('Apr-11', $person1->birthday->format('M-d'));
        $this->assertEquals('male', $person1->gender);

        $person2 = new Person('600471-8174');
        $this->assertEquals('Samordningsnummer', $person2->type);
        $this->assertEquals('Apr-11', $person2->birthday->format('M-d'));
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
    public function testObviousBadNumbers()
    {
        $this->assertFalse((new Organization('1234'))->valid());
        $this->assertFalse((new Person('1234'))->valid());

        $this->assertFalse((new Organization('123456789101112'))->valid());
        $this->assertFalse((new Person('123456789101112'))->valid());

        $this->assertFalse((new Organization('345678-abcd'))->valid());
        $this->assertFalse((new Person('345678-abcd'))->valid());

        $this->assertFalse((new Organization('abcefghijklm'))->valid());
        $this->assertFalse((new Person('abcefghijklm'))->valid());
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
    }

    /** @test */
    public function testUnsuccessfulPersonFormat()
    {
        $this->expectException(PersonException::class);
        (new Person('111111-1111'))->format();
    }

    /** @test */
    public function testUnsuccessfulOrganizationFormat()
    {
        $this->expectException(OrganizationException::class);
        (new Organization('111111-1111'))->format();
    }

    public function testOrganizationUnsetAttribute()
    {
        $this->expectException(ErrorException::class);
        (new Organization('556016-0680'))->test;
    }

    public function testPersonUnsetAttribute()
    {
        $this->expectException(ErrorException::class);
        (new Person('600411-8177'))->test;
    }

    /** @test */
    public function testLaravelValidator()
    {
        if (class_exists(Validator::class)) {
            $this->assertTrue($this->validateLaravel('556016-0680', 'organization'));
            $this->assertTrue($this->validateLaravel('5560160680', 'any'));
            $this->assertTrue($this->validateLaravel('556016-0680'));
            $this->assertFalse($this->validateLaravel('5560160680', 'person'));

            $this->assertTrue($this->validateLaravel('600411-8177', 'person'));
            $this->assertTrue($this->validateLaravel('6004118177', 'any'));
            $this->assertTrue($this->validateLaravel('19600411-8177', 'any'));
            $this->assertFalse($this->validateLaravel('600411-8177', 'organization'));

            $this->assertFalse($this->validateLaravel('aabbcc-ddee', 'any'));
            $this->assertFalse($this->validateLaravel('aabbccddee', 'organization'));
            $this->assertFalse($this->validateLaravel('00aabbccddee', 'person'));
        }
    }

    /** @test */
    public function testLaravelValidatorWithMessage()
    {
        if (class_exists(Validator::class)) {
            $this->assertEquals('The number is not a valid entity.', $this->validateLaravelMessage('aabbcc-ddee', 'any'));
            $this->assertEquals('Ogiltigt organisationsnummer.', $this->validateLaravelMessage('aabbccddee', 'organization', 'Ogiltigt organisationsnummer.'));
            $this->assertEquals('Number är ett ogiltigt personnummer.', $this->validateLaravelMessage('00aabbccddee', 'person', ':Attribute är ett ogiltigt personnummer.'));
            $this->assertEquals('number är ett ogiltigt personnummer.', $this->validateLaravelMessage('00aabbccddee', 'person', ':attribute är ett ogiltigt personnummer.'));
        }
    }

    /** @test */
    public function testCleanerHelper()
    {
        $this->assertEquals('600411-8177', Entity::clean('600411-8177a'));
        $this->assertEquals('6004118177', Entity::clean('600411 8177'));
        $this->assertEquals('600411-8177', Entity::clean('a600411-8177'));
        $this->assertEquals('6004118177', Entity::clean('6004118177'));

        // Assert that a bad value can be good after a cleaning
        $bad1 = '600411-8177a';
        $this->assertFalse((new Person($bad1))->valid());
        $this->assertTrue((new Person(Entity::clean($bad1)))->valid());

        $bad2 = '20600411   8177';
        $this->assertFalse((new Person($bad2))->valid());
        $this->assertTrue((new Person(Entity::clean($bad2)))->valid());
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

    private function validateLaravel($number, $type = null)
    {
        $data = ['number' => $number];
        $validator = Validator::make($data, [
            'number' => sprintf('entity:%s', $type)
        ]);

        return $validator->passes();
    }

    private function validateLaravelMessage($number, $type = null, $message = null)
    {
        $data = ['number' => $number];
        $validator = Validator::make($data, [
            'number' => sprintf('entity:%s', $type)
        ], [
            'number.entity' => $message
        ]);

        $errors = $validator->errors();

        return $errors->first('number');
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
