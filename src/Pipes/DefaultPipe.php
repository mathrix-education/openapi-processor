<?php

namespace Mathrix\OpenAPI\Processor\Pipes;

/**
 * Class DefaultBasePipe.
 * Allow to use a default value in case of the input is empty.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class DefaultPipe extends BasePipe
{
    /**
     * Transform the input.
     *
     * @param string $input
     * @param array $args
     *
     * @return string
     */
    public function transform(?string $input, ...$args): string
    {
        return $input ?? $args[0];
    }
}
