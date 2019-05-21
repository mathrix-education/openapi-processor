<?php

namespace Mathrix\OpenAPI\PreProcessor;

use PHPUnit\Framework\TestCase;

/**
 * Class RendererTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.4-dev
 */
class RendererTest extends TestCase
{
    public function testCompile()
    {
        $file = __DIR__ . "/../fixtures/mathrix-drive/partials/paths/delete_{modelId}.yaml";
        $context = ["model" => "apple"];

        $out = Renderer::make()
            ->setFile($file)
            ->setContext($context)
            ->compile()
            ->getParsedOutput();

        dd($out);
    }
}
