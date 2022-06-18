<?php declare(strict_types=1);

namespace ZFekete\Sweefy\VO\Query;

use JetBrains\PhpStorm\Immutable;
use ZFekete\Sweefy\Enum\Query\Type;
use ZFekete\Sweefy\Service\Query\TypeResolver;

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
