<?php

namespace Monolog\Handler;

/**
 * Capture error_log
 *
 * @param array $args
 */
function error_log(...$args)
{
    $GLOBALS["error_log"][] = $args[0];
}

namespace Mathrix\OpenAPI\Processor;

use PHPUnit\Framework\TestCase;

/**
 * Class BinaryTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class BinaryTest extends TestCase
{
    public function setUp(): void
    {
        // Flush the error log
        $GLOBALS["error_log"] = [];
    }


    public function compileExample($exampleName)
    {
        $bin = realpath(__DIR__ . "/../bin/openapi-processor");
        $cwd = dirname(dirname($bin));
        $outputFile = "$cwd/examples/$exampleName/output.yaml";

        chdir($cwd);

        if (file_exists($outputFile)) {
            unlink($outputFile);
        }

        ob_start();
        $argv = ["./bin/openapi-processor", "--input=examples/$exampleName/index.yaml", "--debug=1"];
        $argc = 3;
        require $bin;
        ob_end_clean();

        return $outputFile;
    }


    public function testPetStore()
    {
        $outputFile = $this->compileExample("petstore");
        $this->assertTrue(file_exists($outputFile));
    }
}
