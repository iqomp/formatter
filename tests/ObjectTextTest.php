<?php
declare(strict_types=1);

namespace Iqomp\Formatter\Tests;

use PHPUnit\Framework\TestCase;
use Iqomp\Formatter\Object\Text;

class ObjectTextTest extends TestCase
{

    /**
     * @dataProvider textPropertyProvider
     */
    public function testTextProperty($property, $text, $expect): void
    {
        $text = new Text($text);
        $result = $text->$property;

        $this->assertEquals($expect, $result);
    }

    public function textPropertyProvider()
    {
        return [
            'property/0' => [
                'clean',
                'Text with <strong>html</strong> tag',
                'Text with html tag'
            ],
            'property/1' => [
                'safe',
                'Text with <strong>html</strong> tag',
                htmlspecialchars('Text with <strong>html</strong> tag', ENT_QUOTES)
            ]
        ];
    }

    /**
     * @dataProvider textMethodProvider
     */
    public function testTextMethod($method, $text, $expect, $args=[]): void
    {
        $text = new Text($text);

        $result = call_user_func_array([$text, $method], $args);

        $this->assertEquals($expect, $result);
    }

    public function textMethodProvider()
    {
        $data = [
            '__toString/0' => [
                'text'   => 'some text',
                'expect' => 'some text',
            ],
            'chars/0' => [
                'text' => 'some text',
                'expect' => 'some',
                'args' => [4]
            ],
            'getClean/0' => [
                'text' => 'some text!',
                'expect' => 'some text'
            ],
            'getSafe/0' => [
                'text' => 'some <em>text</em>',
                'expect' => htmlspecialchars('some <em>text</em>', ENT_QUOTES)
            ],
            'words/0' => [
                'text' => 'some text in the box',
                'expect' => 'some text in',
                'args' => [3]
            ]
        ];

        $result = [];
        foreach($data as $key => $val){
            $methods = explode('/', $key);
            $method = $methods[0];

            $result[$key] = [
                $method,
                $val['text'],
                $val['expect'],
                $val['args'] ?? []
            ];
        }

        return $result;
    }

    public function testJsonSerialize(): void
    {
        $object = (object)[
            'name' => new Text('Iqbal Fauzi')
        ];

        $expect = json_encode(['name'=>'Iqbal Fauzi']);
        $result = json_encode($object);

        $this->assertEquals($expect, $result);
    }
}
