<?php

namespace Gibass\DomainMakerBundle\Builder\Registry;

use Gibass\DomainMakerBundle\Builder\Interface\BuilderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

readonly class BuilderRegistry
{
    public function __construct(#[AutowireLocator(BuilderInterface::class)] private ContainerInterface $locator)
    {
    }

    public function getBuilder(string $name): ?BuilderInterface
    {
        if ($this->locator->has($name)) {
            return $this->locator->get($name);
        }

        return null;
    }
}
