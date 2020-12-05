<?php
declare(strict_types=1);

namespace Iqomp\Formatter\Tests;

use PHPUnit\Framework\TestCase;
use Iqomp\Formatter\Handler;

use Iqomp\Formatter\Object\{
    DateTime,
    Embed,
    Interval,
    Number,
    Std,
    Text
};

class Custom
{
    public static function toPI($val)
    {
        return '3.14';
    }
}

class HandlerTest extends TestCase
{
    /**
     * @dataProvider booleanProvider
     */
    public function testBoolean($expect, $val): void
    {
        $val = Handler::boolean($val);
        $this->assertEquals($expect, $val);
    }

    public function booleanProvider(): array
    {
        return [
            'string/0' => [false, '0'],
            'string/1' => [true, '1'],
            'object/0' => [true, (object)[]],
            'object/1' => [true, (object)['a'=>'b']],
            'array/0'  => [false, []],
            'array/1'  => [true, ['a']]
        ];
    }

    public function testCloneSingle(): void
    {
        $object = (object)[
            'user_id' => 11
        ];
        $format = [
            'type' => 'clone',
            'source' => [
                'field' => 'user_id'
            ]
        ];

        $val = Handler::clone(null, 'user', $object, $format, []);

        $this->assertEquals($val, $object->user_id);
    }

    public function testCloneSingleType(): void
    {
        $object = (object)[
            'user_id' => 11
        ];
        $format = [
            'type' => 'clone',
            'source' => [
                'field' => 'user_id',
                'type'  => 'number'
            ]
        ];

        $val = Handler::clone(null, 'user', $object, $format, []);

        $this->assertInstanceOf(Number::class, $val);
    }

    public function testCloneSingleTypeOriginal(): void
    {
        $object = (object)[
            'user_id' => new Number(11)
        ];
        $format = [
            'type' => 'clone',
            'source' => [
                'field' => 'user_id'
            ]
        ];

        $val = Handler::clone(null, 'user', $object, $format, []);

        $this->assertInstanceOf(Number::class, $val);
    }

    public function testCloneSingleTypeConvert(): void
    {
        $object = (object)[
            'user_id' => new Number(11)
        ];
        $format = [
            'type' => 'clone',
            'source' => [
                'field' => 'user_id',
                'type' => 'text'
            ]
        ];

        $val = Handler::clone(null, 'user', $object, $format, []);

        $this->assertInstanceOf(Text::class, $val);
    }

    public function testCloneSingleTypeConvertedValue(): void
    {
        $object = (object)[
            'user_id' => new Number(11)
        ];
        $format = [
            'type' => 'clone',
            'source' => [
                'field' => 'user_id',
                'type' => 'text'
            ]
        ];

        $val = Handler::clone(null, 'user', $object, $format, []);

        $this->assertEquals((string)$val, '11');
    }

    public function testCloneMany(): void
    {
        $object = (object)[
            'first_name' => 'Iqbal',
            'last_name' => 'Fauzi'
        ];
        $format = [
            'type' => 'clone',
            'sources' => [
                'first' => [
                    'field' => 'first_name'
                ],
                'last' => [
                    'field' => 'last_name'
                ]
            ]
        ];

        $val = Handler::clone(null, 'user', $object, $format, []);

        $expect = (object)['first'=>'Iqbal','last'=>'Fauzi'];

        $this->assertEquals($expect, $val);
    }

    public function testCloneManyType(): void
    {
        $object = (object)[
            'first_name' => 'Iqbal',
            'last_name' => 'Fauzi'
        ];
        $format = [
            'type' => 'clone',
            'sources' => [
                'first' => [
                    'field' => 'first_name',
                    'type' => 'text'
                ],
                'last' => [
                    'field' => 'last_name'
                ]
            ]
        ];

        $val = Handler::clone(null, 'user', $object, $format, []);

        $this->assertInstanceOf(Text::class, $val->first);
    }

    public function testCloneManyTypeOriginal(): void
    {
        $object = (object)[
            'first_name' => 'Iqbal',
            'last_name' => new Text('Fauzi')
        ];
        $format = [
            'type' => 'clone',
            'sources' => [
                'first' => [
                    'field' => 'first_name'
                ],
                'last' => [
                    'field' => 'last_name'
                ]
            ]
        ];

        $val = Handler::clone(null, 'user', $object, $format, []);

        $this->assertInstanceOf(Text::class, $val->last);
    }

    public function testCloneManyTypeConvert(): void
    {
        $object = (object)[
            'first_name' => 'Iqbal',
            'last_name' => new Text('Fauzi'),
            'age'       => new Text('31')
        ];
        $format = [
            'type' => 'clone',
            'sources' => [
                'first' => [
                    'field' => 'first_name'
                ],
                'last' => [
                    'field' => 'last_name'
                ],
                'age' => [
                    'field' => 'age',
                    'type' => 'number'
                ]
            ]
        ];

        $val = Handler::clone(null, 'user', $object, $format, []);

        $this->assertInstanceOf(Number::class, $val->age);
    }

    public function testCloneManyTypeConvertedValue(): void
    {
        $object = (object)[
            'first_name' => 'Iqbal',
            'last_name' => new Text('Fauzi'),
            'age'       => new Text('31')
        ];
        $format = [
            'type' => 'clone',
            'sources' => [
                'first' => [
                    'field' => 'first_name'
                ],
                'last' => [
                    'field' => 'last_name'
                ],
                'age' => [
                    'field' => 'age',
                    'type' => 'number'
                ]
            ]
        ];

        $val = Handler::clone(null, 'user', $object, $format, []);

        $this->assertEquals(31, $val->age->final);
    }

    public function testCustom(): void
    {
        $object = (object)[
            'age' => '31'
        ];
        $format = [
            'type' => 'custom',
            'handler' => 'Iqomp\\Formatter\\Tests\\Custom::toPI'
        ];

        $val = Handler::custom($object->age, 'age', $object, $format, []);

        $this->assertEquals('3.14', $val);
    }

    public function testDate(): void
    {
        $object = (object)[
            'created' => '2020-01-01'
        ];
        $format = [
            'type' => 'date',
            'timezone' => 'UTC'
        ];

        $val = Handler::date($object->created, 'created', $object, $format, []);

        $this->assertInstanceOf(DateTime::class, $val);
    }

    public function testDelete(): void
    {
        $object = (object)[
            'updated' => '2020-02-01',
            'created' => '2020-01-01'
        ];
        $format = [
            'type' => 'delete'
        ];

        $val = Handler::delete($object->updated, 'updated', $object, $format, []);

        $expect = (object)['created' => '2020-01-01'];

        $this->assertEquals($expect, $object);
    }

    public function testEmbed(): void
    {
        $object = (object)[
            'embed' => 'https://youtu.be/dQw4w9WgXcQ'
        ];
        $format = [
            'type' => 'embed'
        ];

        $val = Handler::embed($object->embed, 'embed', $object, $format, []);

        $this->assertInstanceOf(Embed::class, $val);
    }

    public function testInterval(): void
    {
        $object = (object)[
            'since' => 'P2Y4DT6H8M'
        ];
        $format = [
            'type' => 'interval'
        ];

        $val = Handler::interval($object->since, 'since', $object, $format, []);

        $this->assertInstanceOf(Interval::class, $val);
    }

    public function testMultipleTextType(): void
    {
        $object = (object)[
            'content' => 'Lorem' . PHP_EOL . 'Ipsum'
        ];
        $format = [
            'type' => 'multiple-text'
        ];

        $val = Handler::multipleText($object->content, 'content', $object, $format, []);

        $this->assertIsArray($val);
    }

    public function testMultipleTextLength(): void
    {
        $object = (object)[
            'content' => 'Lorem' . PHP_EOL . 'Ipsum'
        ];
        $format = [
            'type' => 'multiple-text'
        ];

        $val = Handler::multipleText($object->content, 'content', $object, $format, []);

        $this->assertEquals(2, count($val));
    }

    public function testMultipleTextItemType(): void
    {
        $object = (object)[
            'content' => 'Lorem' . PHP_EOL . 'Ipsum'
        ];
        $format = [
            'type' => 'multiple-text'
        ];

        $val = Handler::multipleText($object->content, 'content', $object, $format, []);

        $this->assertInstanceOf(Text::class, $val[0]);
    }

    public function testMultipleTextCommaSep(): void
    {
        $object = (object)[
            'content' => 'Lorem,Ipsum'
        ];
        $format = [
            'type' => 'multiple-text',
            'separator' => ','
        ];

        $val = Handler::multipleText($object->content, 'content', $object, $format, []);

        $this->assertEquals(2, count($val));
    }

    public function testMultipleTextJSONSep(): void
    {
        $object = (object)[
            'content' => '["Lorem","Ipsum"]'
        ];
        $format = [
            'type' => 'multiple-text',
            'separator' => 'json'
        ];

        $val = Handler::multipleText($object->content, 'content', $object, $format, []);

        $this->assertEquals(2, count($val));
    }

    public function testNumber(): void
    {
        $object = (object)[
            'age' => '31'
        ];
        $format = [
            'type' => 'number'
        ];

        $val = Handler::number($object->age, 'age', $object, $format, []);

        $this->assertInstanceOf(Number::class, $val);
    }

    public function testText(): void
    {
        $object = (object)[
            'name' => 'Iqbal Fauzi'
        ];
        $format = [
            'type' => 'text'
        ];

        $val = Handler::text($object->name, 'name', $object, $format, []);

        $this->assertInstanceOf(Text::class, $val);
    }

    public function testJsonInvalid(): void
    {
        $object = (object)[
            'addr' => '"Not JSON'
        ];
        $format = [
            'type' => 'json'
        ];

        $val = Handler::json($object->addr, 'addr', $object, $format, []);

        $this->assertNull($val);
    }

    public function testJsonValid(): void
    {
        $object = (object)[
            'addr' => '"Valid JSON"'
        ];
        $format = [
            'type' => 'json'
        ];

        $val = Handler::json($object->addr, 'addr', $object, $format, []);

        $this->assertEquals("Valid JSON", $val);
    }

    public function testJsonObject(): void
    {
        $object = (object)[
            'addr' => '{"text":"Valid JSON"}'
        ];
        $format = [
            'type' => 'json'
        ];

        $val = Handler::json($object->addr, 'addr', $object, $format, []);

        $this->assertIsObject($val);
    }

    public function testJsonWithFormatText(): void
    {
        $object = (object)[
            'user' => '{"name":"Iqbal Fauzi"}'
        ];
        $format = [
            'type' => 'json',
            'format' => 'std-name'
        ];

        $val = Handler::json($object->user, 'user', $object, $format, []);

        $name = $val->name;

        $this->assertInstanceOf(Text::class, $name);
    }

    public function testJsonWithFormatTextArray(): void
    {
        $object = (object)[
            'user' => '[{"name":"Iqbal Fauzi"}]'
        ];
        $format = [
            'type' => 'json',
            'format' => 'std-name'
        ];

        $val = Handler::json($object->user, 'user', $object, $format, []);

        $name = $val[0]->name;

        $this->assertInstanceOf(Text::class, $name);
    }

    public function testJoinStrings(): void
    {
        $object = (object)[];
        $format = [
            'type' => 'join',
            'separator' => ' ',
            'fields' => ['My', 'name', 'is', 'Khan']
        ];

        $val = Handler::join('', '', $object, $format, []);

        $this->assertEquals('My name is Khan', $val);
    }

    public function testJoinStringsProp(): void
    {
        $object = (object)[
            'name' => 'Khan'
        ];
        $format = [
            'type' => 'join',
            'separator' => ' ',
            'fields' => ['My', 'name', 'is', '$name']
        ];

        $val = Handler::join('', '', $object, $format, []);

        $this->assertEquals('My name is Khan', $val);
    }

    public function testJoinStringsSubProp(): void
    {
        $object = (object)[
            'user' => [
                'name' => 'Khan',
                'id' => 12
            ]
        ];
        $format = [
            'type' => 'join',
            'separator' => ' ',
            'fields' => ['My', 'name', 'is', '$user.name']
        ];

        $val = Handler::join('', '', $object, $format, []);

        $this->assertEquals('My name is Khan', $val);
    }

    public function testRenameOldRemoved(): void
    {
        $object = (object)[
            'nams' => 'Khan'
        ];
        $format = [
            'type' => 'rename',
            'to' => 'name'
        ];

        $expect = (object)['name' => 'Khan'];

        Handler::rename($object->nams, 'nams', $object, $format, []);

        $this->assertEquals($expect, $object);
    }

    public function testStdId(): void
    {
        $object = (object)[
            'user' => 1
        ];

        $format = [
            'type' => 'std-id'
        ];

        $val = Handler::stdId($object->user, 'user', $object, $format, []);

        $this->assertInstanceOf(Std::class, $val);
    }

    public function testSwitchEq(): void
    {
        $object = (object)[
            'cmp' => 1,
            'val' => 1
        ];
        $format = [
            'type' => 'switch',
            'case' => [
                'r1' => [
                    'field' => 'cmp',
                    'operator' => '=',
                    'expected' => 1,
                    'result' => [
                        'type' => 'number'
                    ]
                ],
                'r2' => [
                    'field' => 'cmp',
                    'operator' => '=',
                    'expected' => 2,
                    'result' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ];

        $val = Handler::switch($object->val, 'val', $object, $format, []);

        $this->assertInstanceOf(Number::class, $val);
    }

    public function testSwitchNEq(): void
    {
        $object = (object)[
            'cmp' => 1,
            'val' => 1
        ];
        $format = [
            'type' => 'switch',
            'case' => [
                'r1' => [
                    'field' => 'cmp',
                    'operator' => '!=',
                    'expected' => 1,
                    'result' => [
                        'type' => 'number'
                    ]
                ],
                'r2' => [
                    'field' => 'cmp',
                    'operator' => '!=',
                    'expected' => 2,
                    'result' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ];

        $val = Handler::switch($object->val, 'val', $object, $format, []);

        $this->assertInstanceOf(Text::class, $val);
    }

    public function testSwitchBt(): void
    {
        $object = (object)[
            'cmp' => 5,
            'val' => 1
        ];
        $format = [
            'type' => 'switch',
            'case' => [
                'r1' => [
                    'field' => 'cmp',
                    'operator' => '>',
                    'expected' => 5,
                    'result' => [
                        'type' => 'number'
                    ]
                ],
                'r2' => [
                    'field' => 'cmp',
                    'operator' => '>',
                    'expected' => 4,
                    'result' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ];

        $val = Handler::switch($object->val, 'val', $object, $format, []);

        $this->assertInstanceOf(Text::class, $val);
    }

    public function testSwitchSt(): void
    {
        $object = (object)[
            'cmp' => 5,
            'val' => 1
        ];
        $format = [
            'type' => 'switch',
            'case' => [
                'r1' => [
                    'field' => 'cmp',
                    'operator' => '<',
                    'expected' => 5,
                    'result' => [
                        'type' => 'number'
                    ]
                ],
                'r2' => [
                    'field' => 'cmp',
                    'operator' => '<',
                    'expected' => 6,
                    'result' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ];

        $val = Handler::switch($object->val, 'val', $object, $format, []);

        $this->assertInstanceOf(Text::class, $val);
    }

    public function testSwitchStoEq(): void
    {
        $object = (object)[
            'cmp' => 5,
            'val' => 1
        ];
        $format = [
            'type' => 'switch',
            'case' => [
                'r1' => [
                    'field' => 'cmp',
                    'operator' => '<=',
                    'expected' => 5,
                    'result' => [
                        'type' => 'number'
                    ]
                ],
                'r2' => [
                    'field' => 'cmp',
                    'operator' => '<=',
                    'expected' => 6,
                    'result' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ];

        $val = Handler::switch($object->val, 'val', $object, $format, []);

        $this->assertInstanceOf(Number::class, $val);
    }

    public function testSwitchBtoEq(): void
    {
        $object = (object)[
            'cmp' => 5,
            'val' => 1
        ];
        $format = [
            'type' => 'switch',
            'case' => [
                'r1' => [
                    'field' => 'cmp',
                    'operator' => '>=',
                    'expected' => 6,
                    'result' => [
                        'type' => 'number'
                    ]
                ],
                'r2' => [
                    'field' => 'cmp',
                    'operator' => '>=',
                    'expected' => 5,
                    'result' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ];

        $val = Handler::switch($object->val, 'val', $object, $format, []);

        $this->assertInstanceOf(Text::class, $val);
    }

    public function testSwitchIn(): void
    {
        $object = (object)[
            'cmp' => 5,
            'val' => 1
        ];
        $format = [
            'type' => 'switch',
            'case' => [
                'r1' => [
                    'field' => 'cmp',
                    'operator' => 'in',
                    'expected' => [1,2,3],
                    'result' => [
                        'type' => 'number'
                    ]
                ],
                'r2' => [
                    'field' => 'cmp',
                    'operator' => 'in',
                    'expected' => [5,6,7],
                    'result' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ];

        $val = Handler::switch($object->val, 'val', $object, $format, []);

        $this->assertInstanceOf(Text::class, $val);
    }

    public function testSwitchNIn(): void
    {
        $object = (object)[
            'cmp' => 5,
            'val' => 1
        ];
        $format = [
            'type' => 'switch',
            'case' => [
                'r1' => [
                    'field' => 'cmp',
                    'operator' => '!in',
                    'expected' => [1,2,3],
                    'result' => [
                        'type' => 'number'
                    ]
                ],
                'r2' => [
                    'field' => 'cmp',
                    'operator' => '!in',
                    'expected' => [5,6,7],
                    'result' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ];

        $val = Handler::switch($object->val, 'val', $object, $format, []);

        $this->assertInstanceOf(Number::class, $val);
    }
}
