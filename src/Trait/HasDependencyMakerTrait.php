<?php

namespace Gibass\DomainMakerBundle\Trait;

use Gibass\DomainMakerBundle\Contracts\MakerInterface;

trait HasDependencyMakerTrait
{
    protected array $dependencies = [];

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function addDependency(?MakerInterface $maker): void
    {
        if ($maker && empty($this->dependencies[get_class($maker)])) {
            $this->dependencies[get_class($maker)] = $maker;
        }
    }
}
