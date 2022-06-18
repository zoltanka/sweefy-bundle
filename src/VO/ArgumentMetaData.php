<?php declare(strict_types=1);

namespace ZFekete\Sweefy\VO;

use Closure;
use JetBrains\PhpStorm\Immutable;

#[Immutable]
class ArgumentMetaData
{
    public function __construct(
        protected readonly string  $name,
        protected readonly bool    $allowsNull,
        protected readonly string  $type,
        protected readonly Closure $caster
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isAllowsNull(): bool
    {
        return $this->allowsNull;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCaster(): Closure
    {
        return $this->caster;
    }
}
