<?php declare(strict_types=1);

namespace ZFekete\SweefyBundle\Tests\Unit;

use ZFekete\SweefyBundle\SweefyBundle;
use PHPUnit\Framework\TestCase;

class SweefyBundleTest extends TestCase
{
    protected SweefyBundle $bundle;

    public function setUp(): void
    {
        $this->bundle = new SweefyBundle();
    }

    public function testGetContainerExtension(): void
    {
        self::assertNotNull($this->bundle->getContainerExtension());
    }

    public function testGetPath(): void
    {
        $path = $this->bundle->getPath();

        self::assertNotEmpty($path);
    }
}
