<?php

namespace Gibass\DomainMakerBundle\Trait;

use Gibass\DomainMakerBundle\Generator\ConfigGenerator;
use Gibass\DomainMakerBundle\Generator\MakerGenerator;
use Gibass\DomainMakerBundle\Manager\DocumentManager;
use Gibass\DomainMakerBundle\Manager\MakerManager;
use Gibass\DomainMakerBundle\Structure\StructureConfig;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Component\Console\Input\InputInterface;

trait MakerConfigurableTrait
{
    public function __construct(
        StructureConfig $config,
        MakerManager $makerManager,
        DocumentManager $manager,
        MakerGenerator $generator,
        private readonly ConfigGenerator $configGenerator
    ) {
        parent::__construct($config, $makerManager, $manager, $generator);
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->initConfigs();
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        foreach ($this->getDependencies() as $dependency) {
            $this->generator->generate($dependency);
        }

        $this->generator->generate($this);
        $this->configGenerator->generate();

        $this->generator->write();
        $this->writeSuccessMessage($io);
    }
}
