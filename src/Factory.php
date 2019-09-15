<?php

declare(strict_types=1);

namespace Mathrix\OpenAPI\Processor;

abstract class Factory
{
    public static function make()
    {
        return new static();
    }
}
