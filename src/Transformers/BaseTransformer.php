<?php

namespace Mathrix\OpenAPI\Processor\Transformers;

/**
 * Class BaseTransformer.
 *
 * @author Mathieu Bour <mathieu@mathrix.fr>
 * @copyright Mathrix Education SA.
 * @since 0.9.0
 */
abstract class BaseTransformer
{
    public abstract function __invoke(array $data);
}
