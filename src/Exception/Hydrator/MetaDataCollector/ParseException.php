<?php declare(strict_types=1);

namespace ZFekete\SweefyBundle\Exception\Hydrator\MetaDataCollector;

use ZFekete\SweefyBundle\Exception\Hydrator\MetaDataCollectorException;

class ParseException extends MetaDataCollectorException
{
    public static function notSupportedType(string $type, string $argumentName, string $className): self
    {
        return new self(
            sprintf('Type "%s", for argument "%s" of "%s" is not supported"', $type, $argumentName, $className)
        );
    }
}
