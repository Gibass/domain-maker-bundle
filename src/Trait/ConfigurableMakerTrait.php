<?php

namespace Gibass\DomainMakerBundle\Trait;

trait ConfigurableMakerTrait
{
    public function getConfigContents(): array
    {
        return $this->configManager->getConfigContents();
    }
}
