<?php

declare(strict_types=1);

namespace Mathrix\OpenAPI\Processor\Pipes;

use PHPUnit\Framework\TestCase;

/**
 *
 */
class PluralizePipeTest extends TestCase
{
    public function dataProvider()
    {
        return [
            ["apple", "apples"],
            ["plan", "plans"],
            ["child", "children"],
        ];
    }

    /**
     * @param $singular
     * @param $expected
     *
     * @dataProvider dataProvider
     */
    public function testTransform($singular, $expected)
    {
        $this->assertEquals($expected, (new PluralizePipe())->transform($singular));
    }
}
