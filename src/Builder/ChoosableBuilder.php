<?php

namespace Gibass\DomainMakerBundle\Builder;

use Gibass\DomainMakerBundle\Contracts\ChoosableMakerInterface;
use Gibass\DomainMakerBundle\Contracts\MakerInterface;
use Gibass\DomainMakerBundle\Exception\InvalidMakerException;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Input\InputInterface;

readonly class ChoosableBuilder extends DependencyBuilder
{
    public function interact(MakerInterface $maker, InputInterface $input, ConsoleStyle $io): void
    {
        if (!$maker instanceof ChoosableMakerInterface) {
            throw new InvalidMakerException(ChoosableMakerInterface::class);
        }

        if ($maker->isDependency()) {
            $maker->interactOptions($io);
            $maker->interactInput($io);
        }
    }
}
