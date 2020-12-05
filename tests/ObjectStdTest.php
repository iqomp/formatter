<?php
declare(strict_types=1);

namespace Iqomp\Formatter\Tests;

use PHPUnit\Framework\TestCase;
use Iqomp\Formatter\Object\Std;

class ObjectStdTest extends TestCase
{
    public function testJsonSerialize(): void
    {
        $object = [
            'user' => new Std(1)
        ];
        $expect = json_encode(['user'=>['id'=>1]]);
        $result = json_encode($object);

        $this->assertEquals($expect, $result);
    }
}
