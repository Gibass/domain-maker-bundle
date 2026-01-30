<?php

namespace Gibass\DomainMakerBundle\Test\Helper\Kernel;

use App\Core\Infrastructure\Symfony\Kernel;
use Gibass\DomainMakerBundle\DomainMakerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MakerTestKernel extends Kernel
{
    public const string TMP_DIR = __DIR__ . '/../../tmp';

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new MakerBundle(),
            new DomainMakerBundle(),
        ];
    }

    /**
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) use ($loader) {
            $container->loadFromExtension('domain_maker', [
                'parameters' => [
                    'dir' => [
                        'src' => self::TMP_DIR . '/src/',
                        'config' => self::TMP_DIR . '/config/',
                    ],
                ],
            ]);
        });
    }
}
