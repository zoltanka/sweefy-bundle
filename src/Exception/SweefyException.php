<?php declare(strict_types=1);

namespace ZFekete\Sweefy\Exception;

use Exception;
use Throwable;

class SweefyException extends Exception
{
    public static function unknownReason(?Throwable $previous = null): static
    {
        return new self('Unknown error occurred', previous: $previous);
    }
}
