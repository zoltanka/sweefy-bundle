<?php declare(strict_types=1);

namespace ZFekete\Sweefy\Service;

use ZFekete\Sweefy\Enum\Query\Type;
use ZFekete\Sweefy\Exception\NonUniqueException;
use ZFekete\Sweefy\Exception\NoResultException;
use BackedEnum;
use Doctrine\DBAL\Exception as DbalException;
use Doctrine\DBAL\Result;
use ZFekete\Sweefy\Exception\SweefyException;
use ZFekete\Sweefy\Service\Query\TypeResolver;
use ZFekete\Sweefy\VO\Query\Parameter;
use function array_pop;
use function is_scalar;

/**
 * @template T
 */
class Query
{
    protected ?string $sql = null;

    /**
     * @var Parameter[]
     *
     * @psalm-var Parameter
     */
    protected array $parameters = [];

    public function __construct(
        protected readonly Sweefy $conn
    ) {

    }

    public function setSql(string $sql): static
    {
        $this->sql = $sql;

        return $this;
    }

    public function setParameter(string $key, mixed $value, ?Type $type = null): static
    {
        $parameter = new Parameter($key, $value, $type);

        $this->parameters[$parameter->name] = $parameter;

        return $this;
    }

    public function getParameter(string $key): ?Parameter
    {
        $normalizedName = Parameter::normalizeName($key);

        return $this->parameters[$normalizedName] ?? null;
    }

    public function setParameters(array $parameters): static
    {
        $this->parameters = [];

        foreach ($parameters as $key => $parameterValue) {
            $this->setParameter($key, $parameterValue);
        }

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param class-string<T> $class
     *
     * @throws SweefyException
     *
     * @return T[]
     *
     * @psalm-return list<T>
     */
    public function fetchAll(string $class): array
    {
        $stmtResult = $this->getResult();
        $hydrator   = $this->conn->getHydrator();

        return $hydrator->hydrateAll($stmtResult, $class);
    }

    /**
     *
     * @throws SweefyException
     *
     * @return array[][]
     *
     * @psalm-return list<array<string, mixed>>
     */
    public function fetchAllAssociative(): array
    {
        $stmtResult = $this->getResult();

        try {
            return $stmtResult->fetchAllAssociative();
        } catch (DbalException $e) {
            throw SweefyException::unknownReason($e);
        }
    }

    /**
     * @param class-string<T> $class
     *
     * @throws SweefyException
     *
     * @return T|null
     */
    public function fetchOneOrNull(string $class): ?object
    {
        $stmtResult = $this->getResult();
        $hydrator   = $this->conn->getHydrator();

        return $hydrator->hydrateOne($stmtResult, $class);
    }

    /**
     * @throws SweefyException
     *
     * @return array<string, mixed>|null
     */
    public function fetchOneOrNullAssociative(): ?array
    {
        $stmtResult = $this->getResult();

        try {
            $result = $stmtResult->fetchAssociative();
        } catch (DbalException $e) {
            throw SweefyException::unknownReason($e);
        }

        return $result ?: null;
    }

    /**
     * @param class-string<T> $class
     *
     * @throws SweefyException
     * @throws NoResultException
     * @throws NonUniqueException
     *
     * @return T
     */
    public function fetchSingle(string $class): object
    {
        $result = $this->getResult();

        $hydrator = $this->conn->getHydrator();

        $objects = $hydrator->hydrateAll($result, $class);

        if ($objects === []) {
            throw new NoResultException();
        } elseif (count($objects) > 1) {
            throw new NonUniqueException();
        }

        return array_pop($objects);
    }

    /**
     * @throws SweefyException
     * @throws NoResultException
     * @throws NonUniqueException
     *
     * @return array<string, mixed>
     */
    public function fetchSingleAssociative(): array
    {
        $stmtResult = $this->getResult();

        try {
            $result = $stmtResult->fetchAllAssociative();
        } catch (DbalException $e) {
            throw SweefyException::unknownReason($e);
        }

        if ($result === []) {
            throw new NoResultException();
        } elseif (count($result) > 1) {
            throw new NonUniqueException();
        }

        return array_pop($result);
    }

    /**
     * @throws SweefyException
     *
     * @return Result
     */
    protected function getResult(): Result
    {
        $parameters = $types = [];

        foreach ($this->getParameters() as $parameter) {
            $name     = $parameter->name;
            $value    = $this->castParameterValue($parameter->value);
            $dbalType = TypeResolver::resolve($parameter->type);

            $parameters[$name] = $value;
            $types[$name]      = $dbalType;
        }

        try {
            return $this->conn->getConnection()->executeQuery(
                $this->sql,
                $parameters,
                $types
            );
        } catch (DbalException $e) {
            throw SweefyException::unknownReason($e);
        }
    }

    protected function castParameterValue(mixed $value): mixed
    {
        if (is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $this->castArrayParameterValue($value);
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        return $value;
    }

    protected function castArrayParameterValue(array $parameterValue): array
    {
        foreach ($parameterValue as $key => $val) {
            $val = $parameterValue($this->castParameterValue($val));

            $parameterValue[$key] = is_array($val) ? reset($val) : $val;
        }

        return $parameterValue;
    }
}
