<?php
declare(strict_types=1);

namespace Iqomp\Formatter\Tests;

use PHPUnit\Framework\TestCase;
use Iqomp\Formatter\{
    FormatNotFoundException,
    Formatter,
    HandlerNotFoundException
};
use Iqomp\Config\Fetcher as Config;

use Iqomp\Formatter\Object\{
    Number,
    Text
};

/**
 * TODO
 * - test collective
 * - test external handler
 */
class FormatterTest extends TestCase
{
    public static function setUpBeforeClass(): void{
        Config::addFile(__DIR__ . '/config/formatter.php');
    }

    public function testTypeApply(): void
    {
        $obj = (object)[];
        $num = Formatter::typeApply('number', 1, 'age', $obj, [], []);

        $this->assertInstanceOf(Number::class, $num);
    }

    public function testFormatSingle(): void
    {
        $object = (object)['age' => 12, 'name' => 'Khan'];
        $result = Formatter::format('std-test', $object);

        $this->assertInstanceOf(Number::class, $object->age);
        $this->assertInstanceOf(Text::class, $object->name);
    }

    public function testFormatManyType(){
        $objects = [
            (object)['age' => 12, 'name' => 'Khan'],
            (object)['age' => 13, 'name' => 'Ikhsan']
        ];

        $result = Formatter::formatMany('std-test', $objects);

        $this->assertIsArray($result);
    }

    public function testFormatManyObject(){
        $object  = (object)['age' => 12, 'name' => 'Khan'];
        $objects = [
            $object,
            (object)['age' => 13, 'name' => 'Ikhsan']
        ];

        $result = Formatter::formatMany('std-test', $objects);
        $single = Formatter::format('std-test', $object);

        $this->assertEquals($single, $result[0]);
    }

    public function testFormatManyAsKey(){
        $object  = (object)['id'=>1, 'age' => 12, 'name' => 'Khan'];
        $objects = [
            $object,
            (object)['id'=>2, 'age' => 13, 'name' => 'Ikhsan']
        ];

        $result = Formatter::formatMany('std-test', $objects, [], 'id');
        $single = Formatter::format('std-test', $object);

        $this->assertEquals($single, $result[1]);
    }

    public function testFormatNotFound()
    {
        $object = (object)[];
        $this->expectException(FormatNotFoundException::class);

        Formatter::format('std-nont-exists', $object);
    }

    public function testTypeNotFound()
    {
        $object = (object)['age'=>12];
        $this->expectException(HandlerNotFoundException::class);

        Formatter::format('std-type-not-exists', $object);
    }

    public function testOptRest(): void
    {
        $object = (object)[
            'age' => 12,
            'name' => 'Khan',
            'height' => 178,
            'weight' => 57
        ];

        $result = Formatter::format('std-rest', $object);

        $this->assertInstanceOf(Text::class, $result->name);
        $this->assertInstanceOf(Number::class, $result->age);
        $this->assertInstanceOf(Number::class, $result->height);
        $this->assertInstanceOf(Number::class, $result->weight);
    }

    public function testOptRename(): void
    {
        $object = (object)[
            'user_name' => 'Khan'
        ];

        $result = Formatter::format('std-rename', $object);

        $this->assertObjectNotHasAttribute('user_name', $result);
        $this->assertInstanceOf(Text::class, $result->name);
    }

    public function testOptClone(): void
    {
        $object = (object)[
            'name' => 'Khan'
        ];

        $result = Formatter::format('std-clone', $object);

        $this->assertObjectHasAttribute('fullname', $result);
        $this->assertInstanceOf(Text::class, $result->fullname);
    }
}
