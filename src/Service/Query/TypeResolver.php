<?php declare(strict_types=1);

namespace ZFekete\Sweefy\Service\Query;

use BackedEnum;
use DateInterval;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use ZFekete\Sweefy\Enum\Query\Type;
use function is_bool;

class TypeResolver
{
    public static function infere(mixed $value): Type
    {
        if (is_int($value)) {
            return Type::Int;
        }

        if (is_bool($value)) {
            return Type::Bool;
        }

        if ($value instanceof BackedEnum) {
            return is_int($value->value) ?
                Type::Int :
                Type::String;
        }

        if ($value instanceof DateTimeInterface) {
            return Type::DateTime;
        }

        if ($value instanceof DateInterval) {
            return Type::Interval;
        }

        if (is_array($value)) {
            return is_int(current($value)) ?
                Type::IntArray :
                Type::StringArray;
        }

        return Type::String;
    }

    public static function resolve(Type $type): int|string
    {
        return match ($type) {
            Type::Bool        => Types::BOOLEAN,
            Type::Int         => Types::INTEGER,
            Type::String      => Types::STRING,
            Type::DateTime    => Types::DATETIME_IMMUTABLE,
            Type::Interval    => Types::DATEINTERVAL,
            Type::StringArray => Connection::PARAM_STR_ARRAY,
            Type::IntArray    => Connection::PARAM_INT_ARRAY,
            default => Types::STRING
        };
    }
}
