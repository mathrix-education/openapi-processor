<?php

declare(strict_types=1);

namespace Mathrix\OpenAPI\Processor\Pipes;

use Exception;
use function random_int;

class RandomIntPipe extends BasePipe
{
    /**
     * Transform the input.
     *
     * @param string $input
     * @param array  $args
     *
     * @return string
     *
     * @throws Exception
     */
    public function transform(string $input, ...$args): string
    {
        $min = (int)$input;
        $max = (int)$args[0];

        return (string)random_int($min, $max);
    }
}
