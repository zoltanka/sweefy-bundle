<?php declare(strict_types=1);

namespace ZFekete\SweefyBundle\Tests\Functional\VO;

use JetBrains\PhpStorm\Immutable;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use RegexIterator;
use SplFileInfo;
use function getenv;
use function sprintf;
use function substr;

class CheckImmutableStates extends TestCase
{
    public function testValueObjects(): void
    {
        $appRoot       = getenv('APP_ROOT');
        $rootNamespace = getenv('APP_ROOT_NAMESPACE');

        self::assertNotEmpty(
            $appRoot,
            'APP_ROOT is not set. Run the tests in a docker container with the provided compose files.'
        );
        self::assertNotEmpty(
            $rootNamespace,
            'APP_ROOT_NAMESPACE is not set. Run the tests in a docker container with the provided compose files.'
        );

        $a = new RegexIterator(
            new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appRoot . '/src/VO')),
            '/.*\.php/'
        );

        /** @var SplFileInfo $file */
        foreach ($a as $file) {
            $className = $this->getClassName($file, $appRoot, $rootNamespace);

            try {
                $reflection = new ReflectionClass($className);
            } catch (ReflectionException) {
                self::fail(sprintf('Class "%s" does not found in file: "%s".', $className, $file->getPathname()));
            }

            $attributeRef = $reflection->getAttributes(Immutable::class);

            self::assertNotEmpty(
                $attributeRef,
                sprintf('Class "%s" does not have attribute "%s".', $className, Immutable::class)
            );
        }
    }

    protected function getClassName(SplFileInfo $file, string $appRoot, string $rootNamespace): string
    {
        $absPath                                 = $file->getRealPath();
        $relativeFilePathFromSrcWithoutExtension = substr(
            $absPath,
            strlen($appRoot . '/src/'), // Remove the path to the project folder plus the src folder
            (strlen($file->getExtension()) + 1) * -1 // Until the length of the extension + the dot before
        );

        return $rootNamespace . '\\' . str_replace('/', '\\', $relativeFilePathFromSrcWithoutExtension);
    }

}
