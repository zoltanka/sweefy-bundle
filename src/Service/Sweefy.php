<?php declare(strict_types=1);

namespace ZFekete\SweefyBundle\Service;

use ZFekete\SweefyBundle\Contract\ResultHydratorInterface;
use Doctrine\DBAL\Connection;

class Sweefy
{
    public function __construct(
        protected readonly Connection $connection,
        protected readonly ResultHydratorInterface $hydrator
    ) {
    }

    public function createQuery(string $sql): Query
    {
        $query = new Query($this);

        return $query->setSql($sql);
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }

    public function getHydrator(): ResultHydratorInterface
    {
        return $this->hydrator;
    }
}
