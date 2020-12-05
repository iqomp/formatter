<?php
declare(strict_types=1);

namespace Iqomp\Formatter\Tests;

use PHPUnit\Framework\TestCase;
use Iqomp\Formatter\Object\DateTime;


class ObjectDateTimeTest extends TestCase
{
    /**
     * @dataProvider datePropertyProvider
     */
    public function testDateProperty($prop, $value, $tz, $expect): void
    {
        if($tz)
            $tz = new \DateTimeZone($tz);

        $date = new DateTime($value, $tz);
        $result = $date->$prop;

        $this->assertEquals($expect, $result);
    }

    public function datePropertyProvider(): array
    {
        $time = strtotime('2020-12-04 06:31:23');
        return [
            'timezone/0' => ['timezone', '2020-12-04 06:31:23', null, 'UTC'],
            'timezone/1' => ['timezone', '2020-12-04 06:31:23', 'Asia/Jakarta', 'Asia/Jakarta'],
            'value/0'    => ['value', '2020-12-04 06:31:23', null, '2020-12-04 06:31:23'],
            'time/0'     => ['time', '2020-12-04 06:31:23', null, $time]
        ];
    }

    public function testToString(): void
    {
        $date = new DateTime('2020-12-04 06:31:23');
        $result = (string)$date;
        $expect = '2020-12-04T06:31:23+00:00';

        $this->assertEquals($expect, $result);
    }

    public function testJsonSerialize(): void
    {
        $object = [
            'created' => new DateTime('2020-12-04 06:31:23')
        ];
        $expect = json_encode(['created'=>'2020-12-04T06:31:23+00:00']);
        $result = json_encode($object);

        $this->assertEquals($expect, $result);
    }

    public function testFormat(): void
    {
        $date = new DateTime('2020-12-04 06:31:23');
        $result = $date->format('Y-m');
        $expect = '2020-12';

        $this->assertEquals($expect, $result);
    }
}
