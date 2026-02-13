<?php

namespace Gibass\DomainMakerBundle\Contracts;

use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Symfony\Bundle\MakerBundle\ConsoleStyle;

interface MakerInterface
{
    public function getDomain(): string;

    public function setDomain(string $domain): self;

    public function setName(string $name): self;

    public function interactOptions(ConsoleStyle $io): void;

    public function createClassDetails(): ClassDetails;

    public function getClassDetails(): ClassDetails;

    public function setClassDetails(ClassDetails $classDetails): self;

    public function getBuilderClass(): array;

    public function getTemplate(): string;

    public function getParams(): array;
}
