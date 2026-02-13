<?php

namespace Gibass\DomainMakerBundle\Contracts;

interface ChoosableMakerInterface extends DependencyInterface
{
    public function isChosen(): bool;

    public function setChosen(bool $chosen): self;

    public function getChosenDirectory(): string;
}
