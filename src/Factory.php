<?php

namespace Mathrix\OpenAPI\Processor;

/**
 * Class Factory.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
abstract class Factory
{
    public static function make()
    {
        return new static();
    }
}
