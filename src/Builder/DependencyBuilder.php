<?php

namespace Gibass\DomainMakerBundle\Builder;

use Gibass\DomainMakerBundle\Builder\Interface\BuilderInterface;
use Gibass\DomainMakerBundle\Contracts\DependencyInterface;
use Gibass\DomainMakerBundle\Contracts\MakerInterface;
use Gibass\DomainMakerBundle\Generator\MakerGenerator;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Input\InputInterface;

readonly class DependencyBuilder implements BuilderInterface
{
    public function interact(MakerInterface $maker, InputInterface $input, ConsoleStyle $io): void
    {
    }

    public function generate(MakerInterface $maker, MakerGenerator $generator): void
    {
        if ($maker instanceof DependencyInterface && $maker->needToCreate()) {
            $generator->generate($maker);
        }
    }
}
