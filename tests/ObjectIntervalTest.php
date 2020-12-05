<?php
declare(strict_types=1);

namespace Iqomp\Formatter\Tests;

use PHPUnit\Framework\TestCase;
use Iqomp\Formatter\Object\Interval;

class ObjectIntervalTest extends TestCase
{
    /**
     * @dataProvider intervalPropertyInstanceProvider
     */
    public function testIntervalPropertyInstance($prop, $instance): void
    {
        $itv = new Interval('PT1M');
        $result = $itv->$prop;

        $this->assertInstanceOf($instance, $result);
    }

    public function intervalPropertyInstanceProvider()
    {
        return [
            'DateTime'      => ['DateTime', 'DateTime'],
            'DateInterval'  => ['DateInterval', 'DateInterval']
        ];
    }

    /**
     * @dataProvider intervalPropertyProvider
     */
    public function testIntervalProperty($property, $value, $expect): void
    {
        $itv = new Interval($value);
        $result = $itv->$property;

        $this->assertEquals($expect, $result);
    }

    public function intervalPropertyProvider()
    {
        return [
            'time'  => ['time', 'PT1M', 60],
            'value' => ['value', 'PT1M', 'PT1M']
        ];
    }

    public function testJsonSerialize(): void
    {
        $object = [
            'time' => new Interval('PT1M')
        ];
        $expect = json_encode([
            'time' => [
                'time' => 60,
                'date' => date('c', strtotime('+1 minute')),
                'interval' => '1 minute'
            ]
        ]);
        $result = json_encode($object);

        $this->assertEquals($expect, $result);
    }

    public function testToString()
    {
        $ivt = new Interval('PT1M');
        $result = (string)$ivt;
        $expect = '1 minute';

        $this->assertEquals($expect, $result);
    }

    public function testFormat()
    {
        $ivt = new Interval('PT1M');
        $result = $ivt->format('Y-m-d H:i:s');
        $expect = date('Y-m-d H:i:s', strtotime('+1 minute'));

        $this->assertEquals($expect, $result);
    }

    /**
     * @dataProvider intervalMethodIntervalProvider
     */
    public function testMethodInterval($value, $expect): void
    {
        $ivt = new Interval($value);
        $result = $ivt->interval();

        $this->assertEquals($expect, $result);
    }

    public function intervalMethodIntervalProvider()
    {
        return [
            'y'         => ['P1Y', '1 year'],
            'y(s)'      => ['P2Y', '2 years'],
            'ym'        => ['P1Y1M', '1 year and 1 month'],
            'ym(s)'     => ['P1Y2M', '1 year and 2 months'],
            'ymd'       => ['P1Y2M1D', '1 year 2 months and 1 day'],
            'ymd(s)'    => ['P1Y2M2D', '1 year 2 months and 2 days'],
            'h'         => ['PT1H', '1 hour'],
            'h(s)'      => ['PT2H', '2 hours'],
            'hm'        => ['PT1H1M', '1 hour and 1 minute'],
            'hm(s)'     => ['PT1H2M', '1 hour and 2 minutes'],
            'hms'       => ['PT2H3M1S', '2 hours 3 minutes and 1 second'],
            'hms(s)'    => ['PT1H1M2S', '1 hour 1 minute and 2 seconds'],
            'yhm'       => ['P2YT1H3M', '2 years 1 hour and 3 minutes']
        ];
    }
}
