<?php declare(strict_types=1);

namespace ZFekete\Sweefy\VO;

use JetBrains\PhpStorm\Immutable;

/**
 * @template T
 */
#[Immutable]
class ClassMetaData
{
    /**
     * @param class-string<T>              $className
     * @param array<int, ArgumentMetaData> $args
     */
    public function __construct(
        protected readonly string $className,
        protected readonly array  $args
    ) {
    }

    /**
     * @return class-string<T>
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return array<int, ArgumentMetaData>
     */
    public function getConstructorArgs(): array
    {
        return $this->args;
    }
}
