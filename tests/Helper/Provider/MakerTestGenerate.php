<?php

namespace Gibass\DomainMakerBundle\Test\Helper\Provider;

class MakerTestGenerate
{
    private array $inputs = [];
    private array $args = [];
    private array $files = [];
    private array $filesToCreate = [];

    /** @var MakerTestGenerate[]  */
    private array $makers = [];

    public static function create(): static
    {
        return new static();
    }

    public function createDomain(string $domainName): self
    {
        $this->inputs = ['yes', $domainName];
        return $this;
    }

    public function chooseDomain(int $domainIndex): self
    {
        $this->inputs = ['no', $domainIndex];
        return $this;
    }

    public function getInputs(): array
    {
        return $this->inputs;
    }

    public function addInput(mixed $input): self
    {
        $this->inputs[] = $input;
        return $this;
    }

    public function addInputs(array $inputs): self
    {
        $this->inputs = [...$this->inputs, ...$inputs];
        return $this;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function setArgs(array $args): self
    {
        $this->args = $args;
        return $this;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): self
    {
        $this->files = $files;
        return $this;
    }

    public function getMakers(): array
    {
        return $this->makers;
    }

    public function createFile(array $filesToCreate): self
    {
        $this->filesToCreate[] = $filesToCreate;

        return $this;
    }

    public function getFilesToCreate(): array
    {
        return $this->filesToCreate;
    }
}
