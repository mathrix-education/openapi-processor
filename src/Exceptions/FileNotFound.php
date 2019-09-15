<?php

declare(strict_types=1);

namespace Mathrix\OpenAPI\Processor\Exceptions;

use Exception;
use Throwable;

/**
 * Thrown when a file does not exists.
 */
class FileNotFound extends Exception
{
    public function __construct(string $path, $code = 0, ?Throwable $previous = null)
    {
        return parent::__construct("File $path does not exist.", $code, $previous);
    }
}
