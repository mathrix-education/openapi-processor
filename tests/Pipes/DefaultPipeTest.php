<?php

namespace Mathrix\OpenAPI\Processor\Pipes;

use PHPUnit\Framework\TestCase;

/**
 * Class DefaultPipeTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class DefaultPipeTest extends TestCase
{
    public function provider()
    {
        return [
            ["foo", "foo", "bar"],
            ["bar", null, "bar"]
        ];
    }


    /**
     * @dataProvider provider
     *
     * @param $expected
     * @param array $args
     */
    public function testTransformer($expected, ...$args)
    {
        $this->assertEquals($expected, (new DefaultPipe())->transform(...$args));
    }
}
