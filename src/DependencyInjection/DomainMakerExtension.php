<?php

namespace Gibass\DomainMakerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DomainMakerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (!empty($config['parameters'])) {
            foreach ($config['parameters'] as $key => $value) {
                $this->setParams($container, $key, $value);
            }
        }
    }

    private function setParams(ContainerBuilder $container, string $key, $value): void
    {
        if (\is_array($value)) {
            foreach ($value as $ikey => $iValue) {
                $this->setParams($container, $key . '.' . $ikey, $iValue);
            }
        } else {
            $container->setParameter('domain_maker.' . $key, $value);
        }
    }
}
