<?php

namespace Gibass\DomainMakerBundle\Builder\Interface;

use Gibass\DomainMakerBundle\Contracts\MakerInterface;
use Gibass\DomainMakerBundle\Generator\MakerGenerator;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface BuilderInterface
{
    public function interact(MakerInterface $maker, InputInterface $input, ConsoleStyle $io): void;

    public function generate(MakerInterface $maker, MakerGenerator $generator):void;
}
