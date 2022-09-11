<?php declare(strict_types=1);

namespace ZFekete\SweefyBundle\Exception\Hydrator;

use Exception;
use function sprintf;

class ObjectHydratorException extends Exception
{
    public static function metaDataCollection(string $className, Exception $previous): self
    {
        return new self(sprintf('Error occurred while collecting data from RSO "%s".', $className), previous: $previous);
    }

    public static function argumentIndexMismatch(string $argName, int $argIndex, string $className): self
    {
        return new self(
            sprintf(
                'Argument "%s" was expected on position #%d of the constructor of "%s"',
                $argName,
                $argIndex,
                $className
            )
        );
    }

    public static function argumentNameMismatch(
        string $expectedArgName,
        int    $argPosition,
        string $className,
        string $foundArgName
    ): self {
        return new self(
            sprintf(
                'Argument name "%s" was expected on position #%d of the constructor of "%s". Argument name "%s" found ' .
                'instead.',
                $expectedArgName,
                $argPosition,
                $className,
                $foundArgName,
            )
        );
    }

    public static function unableToFetchData(Exception $e): self
    {
        return new self('Unable to fetch data from result', previous: $e);
    }
}
