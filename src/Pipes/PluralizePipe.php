<?php

namespace Mathrix\OpenAPI\PreProcessor\Pipes;

use Symfony\Component\Inflector\Inflector;

/**
 * Class PluralizePipe.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
class PluralizePipe extends BasePipe
{
    /**
     * Transform the input.
     *
     * @param string $input
     * @param array $args
     *
     * @return string
     */
    public function transform(string $input, ...$args): string
    {
        return Inflector::pluralize($input);
    }
}