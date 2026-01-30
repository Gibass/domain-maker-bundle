<?php

namespace Gibass\DomainMakerBundle\Generator;

use Symfony\Bundle\MakerBundle\Str;

class ClassDetails
{
    private string $name;

    private string $suffix;

    private string $subPath;

    private bool $initialized = false;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = Str::asCamelCase($name);
        return $this;
    }

    public function getSuffix(): string
    {
        return $this->suffix ?? '';
    }

    public function setSuffix(string $suffix): self
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function getSubPath(): string
    {
        return $this->subPath;
    }

    public function setSubPath(string $subPath): self
    {
        $this->subPath = $subPath;
        return $this;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function setInitialized(bool $initialized): self
    {
        $this->initialized = $initialized;
        return $this;
    }
}
