<?php

namespace Mathrix\OpenAPI\PreProcessor;

use PHPUnit\Framework\TestCase;

/**
 * Class LoaderTest.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since
 */
class LoaderTest extends TestCase
{
    public function testLoad()
    {
        $file = __DIR__ . "/../fixtures/mathrix-drive/paths/groups/_.yaml";
        $loader = new FileLoader();
        $result = $loader->load($file);
        dd($result);
    }
}
