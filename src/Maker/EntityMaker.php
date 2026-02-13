<?php

namespace Gibass\DomainMakerBundle\Maker;

use Gibass\DomainMakerBundle\Builder\ChoosableBuilder;
use Gibass\DomainMakerBundle\Builder\HasDependencyBuilder;
use Gibass\DomainMakerBundle\Builder\Registry\BuilderRegistry;
use Gibass\DomainMakerBundle\Contracts\ChoosableMakerInterface;
use Gibass\DomainMakerBundle\Contracts\DependencyInterface;
use Gibass\DomainMakerBundle\Contracts\HasDependencyMakerInterface;
use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Gibass\DomainMakerBundle\Generator\MakerGenerator;
use Gibass\DomainMakerBundle\Maker\Abstract\AbstractMaker;
use Gibass\DomainMakerBundle\Manager\DependencyManager;
use Gibass\DomainMakerBundle\Manager\DocumentManager;
use Gibass\DomainMakerBundle\Structure\StructureConfig;
use Gibass\DomainMakerBundle\Trait\ChoosableMakerTrait;
use Gibass\DomainMakerBundle\Trait\DependencyMakerTrait;
use Gibass\DomainMakerBundle\Trait\HasDependencyMakerTrait;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class EntityMaker extends AbstractMaker implements DependencyInterface, ChoosableMakerInterface, HasDependencyMakerInterface
{
    use DependencyMakerTrait;
    use ChoosableMakerTrait;
    use HasDependencyMakerTrait;

    private RepositoryMaker $repositoryMaker;

    public function __construct(
        StructureConfig                    $config,
        DocumentManager                    $docManager,
        BuilderRegistry                    $builderRegistry,
        MakerGenerator                     $generator,
        private readonly DependencyManager $dependencyManager
    )
    {
        parent::__construct($config, $docManager, $builderRegistry, $generator);
    }

    public static function getCommandName(): string
    {
        return 'maker:entity';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Create a new entity.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Choose a name for your entity');
    }

    public function interactDependencies(InputInterface $input, ConsoleStyle $io): void
    {
        if (!$this->isDependency()) {
            $this->addDependency(
                $this->dependencyManager->autoCreate(
                    input: $input,
                    io: $io,
                    makerClass: RepositoryMaker::class,
                    name: $this->name,
                    domain: $this->domain
                )
            );
        }

        if (isset($this->dependencies[RepositoryMaker::class]) && $this->dependencies[RepositoryMaker::class] instanceof RepositoryMaker) {
            $this->dependencies[RepositoryMaker::class]->setEntity($this);
        }
    }

    public function interactInput(ConsoleStyle $io): void
    {
        $this->name = $this->name ??
            $this->isChosen ?
            $io->choice('Choose an existing Entity:', $this->loadExistingItems()) :
            $io->ask('Enter the new Entity name:');
    }

    public function createClassDetails(): ClassDetails
    {
        return ClassDetails::create(
            name: $this->name,
            namespace: $this->getDomainNamespace() . '\\Domain\\Model\\Entity',
            targetPath: $this->getDomainPath() . '/Domain/Model/Entity'
        );
    }

    public function getBuilderClass(): array
    {
        return [
            HasDependencyBuilder::class,
            ChoosableBuilder::class
        ];
    }

    public function getTemplate(): string
    {
        return $this->config->getTemplate('entity/entity');
    }

    public function getChosenDirectory(): string
    {
        return $this->config->getSrcDir() . $this->domain . '/Domain/Model/Entity/';
    }

    public function getParams(): array
    {
        return [
            'namespace' => $this->getClassDetails()->getNamespace(),
            'className' => $this->getClassDetails()->getClassName(),
            'repository' => $this->getRepositoryMakerParams(),
        ];
    }

    public function setRepository(RepositoryMaker $repositoryMaker): void
    {
        $this->repositoryMaker = $repositoryMaker;
    }

    private function getRepositoryMakerParams(): array
    {
        $this->repositoryMaker = $this->dependencies[RepositoryMaker::class] ?? $this->repositoryMaker;

        return $this->repositoryMaker->getParams();
    }
}
