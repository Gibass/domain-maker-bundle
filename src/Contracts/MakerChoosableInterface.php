<?php

namespace Gibass\DomainMakerBundle\Contracts;

use Gibass\DomainMakerBundle\Exception\NoItemToChooseException;
use Symfony\Bundle\MakerBundle\ConsoleStyle;

interface MakerChoosableInterface
{
    public function isChosen(): bool;

    public function setChosen(bool $chosen): self;

    /** @throws NoItemToChooseException */
    public function chooseInput(ConsoleStyle $io): void;
}
