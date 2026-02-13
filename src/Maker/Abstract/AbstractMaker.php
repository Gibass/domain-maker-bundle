<?php

namespace Gibass\DomainMakerBundle\Maker\Abstract;

use Gibass\DomainMakerBundle\Builder\Registry\BuilderRegistry;
use Gibass\DomainMakerBundle\Contracts\MakerInterface;
use Gibass\DomainMakerBundle\Exception\NoDomainException;
use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Gibass\DomainMakerBundle\Generator\MakerGenerator;
use Gibass\DomainMakerBundle\Manager\DocumentManager;
use Gibass\DomainMakerBundle\Structure\StructureConfig;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker as BaseAbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

abstract class AbstractMaker extends BaseAbstractMaker implements MakerInterface
{
    protected string $name;
    protected string $domain;
    protected ClassDetails $classDetails;

    public function __construct(
        protected readonly StructureConfig    $config,
        protected readonly DocumentManager    $docManager,
        protected readonly BuilderRegistry    $builderRegistry,
        protected readonly MakerGenerator     $generator
    )
    {
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->initializeProperties($input, $command);
        $this->interactDomain($io);

        $this->interactOptions($io);
        $this->setClassDetails($this->createClassDetails());

        foreach ($this->getBuilderClass() as $name) {
            if ($builder = $this->builderRegistry->getBuilder($name)) {
                $builder->interact($this, $input, $io);
            }
        }
    }

    public function interactOptions(ConsoleStyle $io): void
    {
    }

    public function getClassDetails(): ClassDetails
    {
        return $this->classDetails;
    }

    public function setClassDetails(ClassDetails $classDetails): self
    {
        $this->classDetails = $classDetails;
        return $this;
    }

    public function getBuilderClass(): array
    {
        return [];
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        foreach ($this->getBuilderClass() as $name) {
            if ($builder = $this->builderRegistry->getBuilder($name)) {
                $builder->generate($this, $this->generator);
            }
        }

        $this->generator->generate($this);

        $this->generator->write();
        $this->writeSuccessMessage($io);
    }

    protected function interactDomain(ConsoleStyle $io): void
    {
        $create = $io->confirm('Do You want to create a new domain ?');

        $response = $create ?
            $io->ask('Enter the name of your domain :') :
            $io->choice('Choose an existing domain :', $this->loadExistingDomains())
        ;

        if (!is_string($response)) {
            throw new NoDomainException();
        }

        $this->domain = Str::asCamelCase($response);
    }

    protected function getDomainNamespace(): string
    {
        return $this->config->getRootNamespace() . '\\' . $this->domain;
    }

    protected function getDomainPath(): string
    {
        return $this->config->getSrcDir() . $this->domain;
    }

    protected function initializeProperties(InputInterface $input, Command $command): void
    {
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties() as $reflectionProperty) {
            if ($command->getDefinition()->hasArgument($reflectionProperty->getName())) {
                $reflectionProperty->setValue($this, $input->getArgument($reflectionProperty->getName()));
            }
        }
    }

    private function loadExistingDomains(): array
    {
        return $this->docManager->listDirectories($this->config->getSrcDir());
    }
}
