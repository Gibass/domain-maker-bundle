<?php

namespace Gibass\DomainMakerBundle\Builder;

use Gibass\DomainMakerBundle\Builder\Interface\BuilderInterface;
use Gibass\DomainMakerBundle\Contracts\MakerInterface;
use Gibass\DomainMakerBundle\Generator\MakerGenerator;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Input\InputInterface;

readonly class MakerBuilder implements BuilderInterface
{
    public function interact(MakerInterface $maker, InputInterface $input, ConsoleStyle $io): void
    {

        $maker->setClassDetails($maker->createClassDetails());
    }

    public function generate(MakerInterface $maker, MakerGenerator $generator): void
    {
        $generator->generate($maker);
    }
}
