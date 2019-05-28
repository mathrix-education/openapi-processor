<?php

namespace Mathrix\OpenAPI\Processor\Transformers;

use Mathrix\OpenAPI\Processor\Config;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionObject;

/**
 * Class XTagGroupsTransformerTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class XTagGroupsTransformerTest extends TestCase
{
    private $subject;


    public function setUp(): void
    {
        $this->subject = new XTagGroupsTransformer();
    }


    /**
     * @throws ReflectionException
     */
    public function testInvoke()
    {
        $input = [
            "tags" => [
                ["name" => "Giraffes"],
                ["name" => "Dogs"],
                ["name" => "Cats"],
                ["name" => "Visitors"],
                ["name" => "Bookings"],
                ["name" => "Shows"],
            ],
            "x-tagGroups" => [
                ["name" => "Animals", "tags" => ["Giraffes", "Dogs", "Cats"]],
                ["name" => "Users", "tags" => ["Visitors", "Bookings"]],
            ]
        ];

        $expected = $input;
        $expected["x-tagGroups"][] = ["name" => "Misc", "tags" => ["Shows"]];

        // Force property visibility
        $obj = new Config();
        $refObject = new ReflectionObject($obj);
        $refProperty = $refObject->getProperty("configurationData");
        $refProperty->setAccessible(true);
        $refProperty->setValue(null, ["defaultTagGroup" => "Misc"]);

        $actual = ($this->subject)($input);

        $this->assertSame($expected, $actual);
    }
}
