<?php declare(strict_types=1);

namespace ZFekete\SweefyBundle\Service;

use ZFekete\SweefyBundle\Exception\Hydrator\MetaDataCollector\ParseException;
use ZFekete\SweefyBundle\VO\ArgumentMetaData;
use ZFekete\SweefyBundle\VO\ClassMetaData;
use Closure;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use JetBrains\PhpStorm\Immutable;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use function array_key_exists;
use function get_class;

class RSOMetaDataBuilder
{
    /**
     * @var array<string, ClassMetaData>
     */
    protected array $cache = [];

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @throws ParseException In case if the given class has invalid or unsupported signature.
     *
     * @return ClassMetaData<T>
     */
    public function get(string $class): ClassMetaData
    {
        if ($cached = $this->getCache($class)) {
            return $cached;
        }

        return $this->setCache($class, $this->buildMetaData($class));
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @throws ParseException In case if the given class has invalid or unsupported signature.
     *
     * @return ClassMetaData<T>
     */
    protected function buildMetaData(string $className): ClassMetaData
    {
        try {
            $reflection = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException(
                sprintf(
                  'Argument 1 must contains a class-string of an existing class. Given class "%s" does not exist.',
                  $className
                ),
                previous: $e
            );
        }

        $this->validateClass($reflection);

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            throw new InvalidArgumentException(sprintf('RSO "%s" has no constructor defined.', $className));
        }

        $constructorArguments = $constructor->getParameters();
        if ($constructorArguments === []) {
            throw new InvalidArgumentException(sprintf('Constructor of RSO "%s" has no arguments.', $className));
        }

        $args = $this->buildArgs($constructorArguments, $className);

        return new ClassMetaData($className, $args);
    }

    protected function validateClass(ReflectionClass $reflectionClass): void
    {
        $immutableAttribute = $reflectionClass->getAttributes(Immutable::class);

        if ($immutableAttribute === []) {
            throw new InvalidArgumentException(
                sprintf('RSO "%s" must have "%s" attribute.', $reflectionClass->getName(), Immutable::class)
            );
        }
    }

    /**
     * @template T
     *
     * @param ReflectionParameter[]           $parameterReflection
     * @param class-string<T>                 $className
     *
     * @psalm-param list<ReflectionParameter> $parameterReflection
     *
     * @throws ParseException In case if any of the arguments have invalid or not supported signature
     *
     * @return array<int, ArgumentMetaData>
     */
    protected function buildArgs(array $parameterReflection, string $className): array
    {
        $arguments = [];

        foreach ($parameterReflection as $parameter) {
            $type = $parameter->getType();
            if ($type === null) {
                throw new InvalidArgumentException(
                    sprintf('Argument "%s" of RSO "%s" has no type.', $parameter->getName(), $className)
                );
            }

            if ($type instanceof ReflectionNamedType === false) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Argument "%s" of RSO "%s" must have a single type. "%s" type encountered.',
                        $parameter->getName(),
                        $className,
                        get_class($type)
                    )
                );
            }

            $caster = $this->getTypeCaster($type, $parameter, $className);

            $arguments[$parameter->getPosition()] = new ArgumentMetaData(
                $parameter->getName(),
                $type->allowsNull(),
                $type->getName(),
                $caster
            );
        }

        return $arguments;
    }

    /**
     * @template T
     *
     * @param ReflectionNamedType $type
     * @param ReflectionParameter $parameter
     * @param class-string<T>     $className
     *
     * @throws ParseException
     *
     * @return Closure
     */
    protected function getTypeCaster(
        ReflectionNamedType $type,
        ReflectionParameter $parameter,
        string              $className
    ): Closure {
        $typeName = $type->getName();

        return match ($typeName) {
            'string'                                           => $this->castString(...),
            'int'                                              => $this->castInt(...),
            'bool'                                             => $this->castBool(...),
            'float'                                            => $this->castFloat(...),
            DateTime::class                                    => $this->castDateTime(...),
            DateTimeInterface::class, DateTimeImmutable::class => $this->castDateTimeImmutable(...),
            default                                            => throw ParseException::notSupportedType(
                $typeName,
                $parameter->getName(),
                $className
            )
        };
    }

    protected function castInt(mixed $val): int
    {
        return (int) $val;
    }

    protected function castBool(mixed $val): bool
    {
        return (bool) $val;
    }

    protected function castString(mixed $val): string
    {
        return (string) $val;
    }

    protected function castFloat(mixed $val): float
    {
        return (float) $val;
    }

    protected function castDateTime(mixed $val): DateTime
    {
        return new DateTime((string) $val);
    }

    protected function castDateTimeImmutable(mixed $val): DateTimeImmutable
    {
        return new DateTimeImmutable((string) $val);
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return ClassMetaData|null
     */
    public function getCache(string $className): ?ClassMetaData
    {
        return $this->cache[$className] ?? null;
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return bool
     */
    public function hasCache(string $className): bool
    {
        return array_key_exists($className, $this->cache);
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     * @param ClassMetaData   $metaData
     *
     * @return ClassMetaData
     */
    public function setCache(string $className, ClassMetaData $metaData): ClassMetaData
    {
        return $this->cache[$className] = $metaData;
    }
}
