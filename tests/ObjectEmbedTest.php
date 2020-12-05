<?php
declare(strict_types=1);

namespace Iqomp\Formatter\Tests;

use PHPUnit\Framework\TestCase;
use Iqomp\Formatter\Object\Embed;

/**
 * TODO
 * Make test for each provider syntax
 */
class ObjectEmbedTest extends TestCase
{
    /**
     * @dataProvider embedPropertyProvider
     */
    public function testProperty($prop, $expect): void
    {
        $embed = new Embed('https://youtu.be/dQw4w9WgXcQ');
        $result = $embed->$prop;

        $this->assertEquals($expect, $result);
    }

    public function embedPropertyProvider()
    {
        return [
            'height'    => ['height', 314],
            'width'     => ['width', 560],
            'provider'  => ['provider', 'youtube'],
            'url'       => ['url', 'https://www.youtube.com/embed/dQw4w9WgXcQ?rel=0'],
            'value'     => ['value', 'https://youtu.be/dQw4w9WgXcQ'],
            'html'      => ['html', '<iframe '
                    .   'allowFullscreen="1" '
                    .   'frameborder="0" '
                    .   'height="314" '
                    .   'scrolling="no" '
                    .   'src="https://www.youtube.com/embed/dQw4w9WgXcQ?rel=0" '
                    .   'width="560">'
                    . '</iframe>']
        ];
    }

    public function testToString(): void
    {
        $embed = new Embed('https://youtu.be/dQw4w9WgXcQ');
        $result = (string)$embed;
        $expect = 'https://youtu.be/dQw4w9WgXcQ';
        $this->assertEquals($expect, $result);
    }

    public function testJsonSerialize(): void
    {
        $object = [
            'embed' => 'https://youtu.be/dQw4w9WgXcQ'
        ];
        $expect = json_encode(['embed'=>'https://youtu.be/dQw4w9WgXcQ']);
        $result = json_encode($object);

        $this->assertEquals($expect, $result);
    }
}
