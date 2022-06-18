<?php declare(strict_types=1);

namespace ZFekete\Sweefy\Contract;

interface ObjectHydratorInterface
{
    public function hydrateOne(array $columns, string $class): object;

    public function hydrateMore(array $data, string $class): array;
}
