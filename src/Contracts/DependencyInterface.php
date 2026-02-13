<?php

namespace Gibass\DomainMakerBundle\Contracts;

use Symfony\Bundle\MakerBundle\ConsoleStyle;

interface DependencyInterface
{
    public function isDependency(): bool;

    public function setAsDependency(): void;

    public function interactInput(ConsoleStyle $io): void;

    public function needToCreate(): bool;

    public function setNeedToCreate(bool $needToCreate): void;
}
