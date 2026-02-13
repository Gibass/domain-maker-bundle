<?php

namespace Gibass\DomainMakerBundle\Contracts;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Input\InputInterface;

interface HasDependencyMakerInterface
{
    public function interactDependencies(InputInterface $input, ConsoleStyle $io): void;

    public function getDependencies(): array;

    public function addDependency(?MakerInterface $maker): void;
}
