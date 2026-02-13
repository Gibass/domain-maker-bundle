<?php

namespace Gibass\DomainMakerBundle\Builder;

use Gibass\DomainMakerBundle\Builder\Interface\BuilderInterface;
use Gibass\DomainMakerBundle\Contracts\ConfigurableMakerInterface;
use Gibass\DomainMakerBundle\Contracts\MakerInterface;
use Gibass\DomainMakerBundle\Exception\InvalidMakerException;
use Gibass\DomainMakerBundle\Generator\MakerGenerator;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;

readonly class ConfigurableBuilder implements BuilderInterface
{
    public function interact(MakerInterface $maker, InputInterface $input, ConsoleStyle $io): void
    {
    }

    public function generate(MakerInterface $maker, MakerGenerator $generator): void
    {
        if (!$maker instanceof ConfigurableMakerInterface) {
            throw new InvalidMakerException(ConfigurableMakerInterface::class);
        }

        $maker->initConfigs();

        foreach ($maker->getConfigContents() as $key => $configContent) {
            $generator->dumpFile($key, Yaml::dump($configContent));
        }
    }
}
