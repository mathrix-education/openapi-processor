<?php

namespace Mathrix\OpenAPI\PreProcessor\Pipes;

/**
 * Class BasePipe.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
abstract class BasePipe
{
    /**
     * Transform the input.
     *
     * @param string $input
     * @param array $args
     *
     * @return string
     */
    abstract public function transform(string $input, ...$args): string;
}