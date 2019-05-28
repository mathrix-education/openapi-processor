<?php

namespace Mathrix\OpenAPI\Processor\Transformers;

use PHPUnit\Framework\TestCase;

/**
 * Class TagsTransformerTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class TagsTransformerTest extends TestCase
{
    private $subject;


    public function setUp(): void
    {
        $this->subject = new TagsTransformer();
    }


    public function testInvoke()
    {
        $data = ($this->subject)([
            "paths" => [
                "/pets" => []
            ]
        ]);

        $this->assertSame([
            "paths" => [
                "/pets" => []
            ],
            "tags" => [
                [
                    "name" => "Pets",
                    "description" => "The Pets API, which is handled by the `/pets` root API."
                ]
            ]
        ], $data);
    }
}
