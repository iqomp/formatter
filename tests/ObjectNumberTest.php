<?php
declare(strict_types=1);

namespace Iqomp\Formatter\Tests;

use PHPUnit\Framework\TestCase;
use Iqomp\Formatter\Object\Number;

class ObjectNumberTest extends TestCase
{
    /**
     * @dataProvider numberPropertyProvider
     */
    public function testNumberProperty($num, $dec, $property, $expect): void
    {
        $num = new Number($num, $dec);
        $result = $num->$property;

        $this->assertEquals($expect, $result);
    }

    public function numberPropertyProvider(){
        return [
            'value/0'   => ['10',    0, 'value',     '10'],
            'value/1'   => ['10.2',  0, 'value',     '10.2'],
            'value/2'   => ['10.2',  1, 'value',     '10.2'],
            'value/3'   => ['10.2',  2, 'value',     '10.2'],

            'final/0'   => ['10',    0, 'final',     10],
            'final/1'   => ['10.2',  0, 'final',     10],
            'final/2'   => ['10.2',  1, 'final',     10.2],
            'final/3'   => ['10.2',  2, 'final',     10.2],
            'final/4'   => ['10.22', 2, 'final',     10.22],

            'decimal/0' => ['10',    0, 'decimal',   0],
            'decimal/1' => ['10.2',  0, 'decimal',   0],
            'decimal/1' => ['10.2',  1, 'decimal',   1],
            'decimal/1' => ['10.2',  2, 'decimal',   2]
        ];
    }

    /**
     * @dataProvider numberMethodProvider
     */
    public function testNumberMethod($method, $num, $expect, $dec=0, $args=[]): void
    {
        $number = new Number($num, $dec);

        $result = call_user_func_array([$number, $method], $args);

        $this->assertEquals($expect, $result);
    }

    public function numberMethodProvider(){
        $data = [
            '__toString/0' => [
                'value' => '10',
                'expect' => '10'
            ],
            'format/decimal/0' => [
                'expect' => '12.500',
                'args' => [0]
            ],
            'format/decimal/1' => [
                'expect' => '12.500,3',
                'dec'  => 4,
                'args' => [1]
            ],
            'format/decimal/2' => [
                'expect' => '12.500,26',
                'dec'  => 4,
                'args' => [2]
            ],
            'format/decimal/3' => [
                'expect' => '12.500,256',
                'dec'  => 4,
                'args' => [3]
            ],
            'format/separator/0' => [
                'expect' => '12.500',
                'dec'  => 4,
                'args' => []
            ],
            'format/separator/1' => [
                'expect' => '12.500,3',
                'dec'  => 4,
                'args' => [1]
            ],
            'format/separator/2' => [
                'expect' => '12,500.3',
                'dec'  => 4,
                'args' => [1, '.', ',']
            ],
        ];

        $result = [];
        foreach($data as $name => $opts){
            $methods = explode('/', $name);
            $method = $methods[0];

            $result[$name] = [
                $method,
                $opts['value']  ?? '12500.2561',
                $opts['expect'] ?? 10,
                $opts['dec']    ?? 0,
                $opts['args']   ?? []
            ];
        }

        return $result;
    }

    public function testJsonSerialize(): void
    {
        $object = [
            'total' => new Number(1)
        ];
        $expect = json_encode(['total'=>1]);
        $result = json_encode($object);

        $this->assertEquals($expect, $result);
    }

    public function testJsonSerializeDecimal(): void
    {
        $object = [
            'total' => new Number(1.12, 2)
        ];
        $expect = json_encode(['total'=>1.12]);
        $result = json_encode($object);

        $this->assertEquals($expect, $result);
    }
}
