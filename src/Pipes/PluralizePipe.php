<?php

declare(strict_types=1);

namespace Mathrix\OpenAPI\Processor\Pipes;

use Doctrine\Common\Inflector\Inflector;

class PluralizePipe extends BasePipe
{
    /**
     * Transform the input.
     *
     * @param string $input
     * @param array  $args
     *
     * @return string
     */
    public function transform(string $input, ...$args): string
    {
        return Inflector::pluralize($input);
    }
}
