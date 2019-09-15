<?php

declare(strict_types=1);

namespace Mathrix\OpenAPI\Processor\Transformers;

abstract class BaseTransformer
{
    abstract public function __invoke(array $data);
}
