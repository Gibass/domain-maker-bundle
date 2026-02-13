<?php

namespace Gibass\DomainMakerBundle\Builder;

use Gibass\DomainMakerBundle\Builder\Interface\BuilderInterface;
use Gibass\DomainMakerBundle\Builder\Registry\BuilderRegistry;
use Gibass\DomainMakerBundle\Contracts\HasDependencyMakerInterface;
use Gibass\DomainMakerBundle\Contracts\MakerInterface;
use Gibass\DomainMakerBundle\Exception\InvalidMakerException;
use Gibass\DomainMakerBundle\Generator\MakerGenerator;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Input\InputInterface;

readonly class HasDependencyBuilder implements BuilderInterface
{
    public function __construct(private BuilderRegistry $builderRegistry)
    {
    }

    public function interact(MakerInterface $maker, InputInterface $input, ConsoleStyle $io): void
    {
        if (!$maker instanceof HasDependencyMakerInterface) {
            throw new InvalidMakerException(HasDependencyMakerInterface::class);
        }

        $maker->interactDependencies($input, $io);
    }

    public function generate(MakerInterface $maker, MakerGenerator $generator): void
    {
        if (!$maker instanceof HasDependencyMakerInterface) {
            throw new InvalidMakerException(HasDependencyMakerInterface::class);
        }

        foreach ($maker->getDependencies() as $maker) {
            foreach ($maker->getBuilderClass() as $name) {
                if ($builder = $this->builderRegistry->getBuilder($name)) {
                    $builder->generate($maker, $generator);
                }
            }
        }
    }
}
