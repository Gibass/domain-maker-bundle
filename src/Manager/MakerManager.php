<?php

namespace Gibass\DomainMakerBundle\Manager;

use Gibass\DomainMakerBundle\Contracts\MakerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;

readonly class MakerManager
{
    public function __construct(#[AutowireLocator('gibass.maker')] private ContainerInterface $locator)
    {
    }

    public function getMaker(string $name): MakerInterface
    {
        try {
            return $this->locator->get($name);
        } catch (NotFoundExceptionInterface $e) {
            throw new \RuntimeException("The maker $name does not exist.");
        } catch (ContainerExceptionInterface $e) {
            throw new \RuntimeException("An error occurred while retrieving the maker $name: {$e->getMessage()}");
        }
    }
}
