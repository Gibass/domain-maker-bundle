<?php

namespace Gibass\DomainMakerBundle\Contracts;

interface ConfigurableMakerInterface
{
    public function initConfigs(): void;

    public function getConfigContents(): array;
}
