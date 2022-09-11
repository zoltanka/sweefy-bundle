<?php declare(strict_types=1);

namespace ZFekete\SweefyBundle\Service;

use ZFekete\SweefyBundle\Contract\ObjectHydratorInterface;
use ZFekete\SweefyBundle\Exception\Hydrator\MetaDataCollector\ParseException;
use ZFekete\SweefyBundle\Exception\Hydrator\ObjectHydratorException;
use ZFekete\SweefyBundle\VO\ArgumentMetaData;
use ZFekete\SweefyBundle\VO\ClassMetaData;
use Closure;
use InvalidArgumentException;
use function array_keys;
use function array_map;
use function array_values;
use function current;
use function reset;

class ObjectHydrator implements ObjectHydratorInterface
{
    public function __construct(
        protected readonly RSOMetaDataBuilder $metaDataCollector
    ) {
    }

    /**
     * @template T
     *
     * @param array<string, mixed> $columns
     * @param class-string<T>      $class
     *
     * @throws ObjectHydratorException
     *
     * @return T
     */
    public function hydrateOne(array $columns, string $class): object
    {
        if ($columns === []) {
            throw new InvalidArgumentException('No data to hydrate.');
        }

        try {
            $metaData = $this->metaDataCollector->get($class);
        } catch (ParseException $e) {
            throw ObjectHydratorException::metaDataCollection($class, $e);
        }

        $this->checkOrder($columns, $metaData);

        $typeCasters = array_map(fn(ArgumentMetaData $a): Closure => $a->getCaster(), $metaData->getConstructorArgs());

        $args = [];
        foreach (array_values($columns) as $index => $col) {
            $args[] = $typeCasters[$index]($col);
        }

        return new $class(... $args);
    }

    /**
     * @template T
     *
     * @param array<string, mixed> $data
     * @param class-string<T>      $class
     *
     * @throws ObjectHydratorException
     *
     * @return T[]
     *
     * @psalm-return list<T>
     */
    public function hydrateMore(array $data, string $class): array
    {
        if ($data === []) {
            return [];
        }

        try {
            $metaData = $this->metaDataCollector->get($class);
        } catch (ParseException $e) {
            throw ObjectHydratorException::metaDataCollection($class, $e);
        }

        // Reset the internal pointer to be sure we hydrate all.
        reset($data);

        $this->checkOrder(current($data), $metaData);

        $typeCasters = array_map(fn(ArgumentMetaData $a): Closure => $a->getCaster(), $metaData->getConstructorArgs());

        $hydratedObjects = [];
        foreach ($data as $row) {
            $args = [];
            foreach (array_values($row) as $index => $col) {
                $args[] = $typeCasters[$index]($col);
            }
            $hydratedObjects[] = new $class(... $args);
        }

        return $hydratedObjects;
    }

    /**
     * @param array<string, mixed> $row
     * @param ClassMetaData        $classMetaData
     *
     * @throws ObjectHydratorException
     *
     * @return void
     */
    protected function checkOrder(array $row, ClassMetaData $classMetaData): void
    {
        $constructorArguments = $classMetaData->getConstructorArgs();

        foreach (array_keys($row) as $index => $colName) {
            $argumentMetaData = $constructorArguments[$index] ?? null;

            if ($argumentMetaData === null) {
                throw ObjectHydratorException::argumentIndexMismatch($colName, $index, $classMetaData->getClassName());
            }

            if ($colName !== $argumentMetaData->getName()) {
                throw ObjectHydratorException::argumentNameMismatch(
                    $argumentMetaData->getName(), $index, $classMetaData->getClassName(), $colName
                );
            }
        }
    }
}
