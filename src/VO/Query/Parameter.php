<?php declare(strict_types=1);

namespace ZFekete\SweefyBundle\VO\Query;

use JetBrains\PhpStorm\Immutable;
use ZFekete\SweefyBundle\Enum\Query\Type;
use ZFekete\SweefyBundle\Service\Query\TypeResolver;

#[Immutable]
class Parameter
{
    public readonly string $name;
    public readonly Type $type;

    public function __construct(
        string $name,
        public readonly mixed $value,
        ?Type $type = null
    ) {
        $this->name = self::normalizeName($name);

        if ($type === null) {
            $type = TypeResolver::infere($this->value);
        }

        $this->type = $type;
    }

    public static function normalizeName(string $name): string
    {
        return trim($name);
    }
}
