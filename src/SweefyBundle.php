<?php declare(strict_types=1);

namespace ZFekete\SweefyBundle;

use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use ZFekete\SweefyBundle\DependencyInjection\SweefyExtension;

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
