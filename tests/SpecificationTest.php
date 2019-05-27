<?php

namespace Mathrix\OpenAPI\PreProcessor;

use PHPUnit\Framework\TestCase;

/**
 * Class SpecificationTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class SpecificationTest extends TestCase
{
    public function testCompile()
    {
        $specification = new Wrapper(__DIR__ . "/fixtures/mathrix-drive");
        $specification->compile();
    }
}
