<?php declare(strict_types=1);

namespace ZFekete\SweefyBundle\Contract;

use Doctrine\DBAL\Result;

interface ResultHydratorInterface
{
    /**
     * Hydrates the first row from the given Result object into an instance of class given in the second parameter.
     *
     * @template T
     *
     * @param Result          $result
     * @param class-string<T> $class
     *
     * @return <T>|null
     */
    public function hydrateOne(Result $result, string $class): ?object;

    /**
     * @template T
     *
     * @param Result          $result
     * @param class-string<T> $class
     *
     * @return T[]
     *
     * @psalm-return list<T>
     */
    public function hydrateAll(Result $result, string $class): array;
}
