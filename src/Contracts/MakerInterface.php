<?php

namespace Gibass\DomainMakerBundle\Contracts;

use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Symfony\Bundle\MakerBundle\Generator;

interface MakerInterface
{
    public function getId(): ?string;

    public function setDomain(string $domain): self;

    public function getSubNameSpace(): string;

    public function getShortName(): ?string;

    public function getTargetPath(): ?string;

    public function getTemplate(): ?string;

    /** @return MakerInterface[] */
    public function getDependencies(): array;

    public function needToCreate(): bool;

    public function getDetails(): ClassDetails;

    public function initialize(): void;

    public function getParams(): array;
}
