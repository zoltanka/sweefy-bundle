<?php declare(strict_types=1);

namespace ZFekete\Sweefy;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use ZFekete\Sweefy\DependencyInjection\SweefyExtension;

class SweefyBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new SweefyExtension();
    }

    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
