<?php declare(strict_types=1);

namespace ZFekete\SweefyBundle\Service;

use ZFekete\SweefyBundle\Contract\ResultHydratorInterface;
use ZFekete\SweefyBundle\Contract\ObjectHydratorInterface;
use ZFekete\SweefyBundle\Exception\Hydrator\ObjectHydratorException;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Result;

class ResultHydrator implements ResultHydratorInterface
{
    public function __construct(
        protected readonly ObjectHydratorInterface $hydrator
    ) {
    }

    /**
     * @template T
     *
     * @throws ObjectHydratorException
     *
     * @return T|null
     */
    public function hydrateOne(Result $result, string $class): ?object
    {
        try {
            $data = $result->fetchAssociative();

            if ($data === false) {
                return null;
            }

            return $this->hydrator->hydrateOne($data, $class);
        } catch (DbalException $e) {
            throw ObjectHydratorException::unableToFetchData($e);
        }
    }

    /**
     * @template T
     *
     * @throws ObjectHydratorException
     *
     * @return T[]
     *
     * @psalm-return list<T>
     */
    public function hydrateAll(Result $result, string $class): array
    {
        try {
            return $this->hydrator->hydrateMore($result->fetchAllAssociative(), $class);
        } catch (DbalException $e) {
            throw ObjectHydratorException::unableToFetchData($e);
        }
    }
}
