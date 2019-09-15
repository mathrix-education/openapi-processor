<?php

declare(strict_types=1);

namespace Mathrix\OpenAPI\Processor;

use PHPUnit\Framework\TestCase;

/**
 *
 */
class TemplateEngineTest extends TestCase
{
    public function testRender()
    {
        $engine = new TemplateEngine();
        $engine->setContext([
            'var' => 'apple'
        ]);
        $this->assertEquals('apples', $engine->render('{{ var | pluralize }}'));
    }
}
