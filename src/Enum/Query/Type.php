<?php declare(strict_types=1);

namespace ZFekete\Sweefy\Enum\Query;

enum Type
{
    case Int;

    case Bool;

    case String;

    case DateTime;

    case Interval;

    case StringArray;

    case IntArray;
}
