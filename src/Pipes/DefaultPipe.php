<?php

declare(strict_types=1);

namespace Mathrix\OpenAPI\Processor\Pipes;

/**
 * Allow to use a default value in case of the input is empty.
 */
class DefaultPipe extends BasePipe
{
    /**
     * Transform the input.
     *
     * @param string $input
     * @param array  $args
     *
     * @return string
     */
    public function transform(?string $input, ...$args): string
    {
        return $input ?? $args[0];
    }
}
