<?php

namespace Gibass\DomainMakerBundle\Trait;

trait DependencyMakerTrait
{
    private bool $isDependency = false;

    private bool $needToCreate = true;

    public function isDependency(): bool
    {
        return $this->isDependency;
    }

    public function setAsDependency(): void
    {
        $this->isDependency = true;
    }

    public function needToCreate(): bool
    {
        return $this->needToCreate;
    }

    public function setNeedToCreate(bool $needToCreate): void
    {
        $this->needToCreate = $needToCreate;
    }
}
