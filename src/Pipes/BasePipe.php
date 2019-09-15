<?php

declare(strict_types=1);

namespace Mathrix\OpenAPI\Processor\Pipes;

abstract class BasePipe
{
    /**
     * Transform the input.
     *
     * @param string $input
     * @param array  $args
     *
     * @return string
     */
    abstract public function transform(string $input, ...$args): string;
}
