<?php

namespace Gibass\DomainMakerBundle\Maker;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Gibass\DomainMakerBundle\Builder\DependencyBuilder;
use Gibass\DomainMakerBundle\Builder\HasDependencyBuilder;
use Gibass\DomainMakerBundle\Builder\Registry\BuilderRegistry;
use Gibass\DomainMakerBundle\Contracts\DependencyInterface;
use Gibass\DomainMakerBundle\Contracts\HasDependencyMakerInterface;
use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Gibass\DomainMakerBundle\Generator\MakerGenerator;
use Gibass\DomainMakerBundle\Maker\Abstract\AbstractMaker;
use Gibass\DomainMakerBundle\Manager\DependencyManager;
use Gibass\DomainMakerBundle\Manager\DocumentManager;
use Gibass\DomainMakerBundle\Structure\StructureConfig;
use Gibass\DomainMakerBundle\Trait\DependencyMakerTrait;
use Gibass\DomainMakerBundle\Trait\HasDependencyMakerTrait;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class RepositoryMaker extends AbstractMaker implements HasDependencyMakerInterface, DependencyInterface
{
    use HasDependencyMakerTrait;
    use DependencyMakerTrait;

    private EntityMaker $entityMaker;

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
        return 'maker:repository';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Create a new repository.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Choose a name for your repository:')
        ;
    }

    public function interactDependencies(InputInterface $input, ConsoleStyle $io): void
    {
        $this->addDependency(
            $this->dependencyManager->autoCreate(
                input:$input,
                io:$io,
                makerClass: GatewayMaker::class,
                name: $this->getClassDetails()->getClassName(false),
                domain: $this->domain
            ),
        );

        if (!$this->isDependency()) {
            $this->addDependency(
                $this->dependencyManager->interactRequired(
                    input: $input,
                    io: $io,
                    makerClass: EntityMaker::class,
                    message: 'Create or Choose Entity for your repository',
                    domain: $this->domain
                )
            );
        }

        if (isset($this->dependencies[EntityMaker::class]) && $this->dependencies[EntityMaker::class] instanceof EntityMaker) {
            $this->dependencies[EntityMaker::class]->setRepository($this);
        }
    }

    public function interactInput(ConsoleStyle $io): void
    {
        $this->name = $this->name ?? $io->ask('Enter the new repository name: ');
    }

    public function createClassDetails(): ClassDetails
    {
        return ClassDetails::create(
            name: $this->name,
            namespace: $this->getDomainNamespace() . '\\Infrastructure\\Adapter\\Repository',
            targetPath: $this->getDomainPath() . '/Infrastructure/Adapter/Repository',
            suffix: 'Repository',
            extendsClass: ServiceEntityRepository::class,
            useStatements: [ServiceEntityRepository::class, ManagerRegistry::class],
        );
    }

    public function getBuilderClass(): array
    {
        return [
            HasDependencyBuilder::class,
            DependencyBuilder::class
        ];
    }

    public function getTemplate(): string
    {
        return $this->config->getTemplate('repository/repository');
    }

    public function getChosenDirectory(): string
    {
        return $this->config->getSrcDir() . $this->domain . '/Infrastructure/Adapter/Repository/';
    }

    public function getParams(): array
    {
        return [
            'namespace' => $this->getClassDetails()->getNamespace(),
            'className' => $this->getClassDetails()->getClassName(),
            'rootNamespace' => $this->config->getRootNamespace(),
            'useStatements' => $this->getClassDetails()->getUseStatementGenerator(),
            'gateway' => !empty($this->dependencies[GatewayMaker::class]) ? $this->dependencies[GatewayMaker::class]->getParams() : [],
            'entity' => $this->getEntityMakerParams(),
        ];
    }

    public function setEntity(EntityMaker $entityMaker): void
    {
        $this->entityMaker = $entityMaker;
    }

    private function getEntityMakerParams(): array
    {
        $this->entityMaker = $this->dependencies[EntityMaker::class] ?? $this->entityMaker;

        return [
            'namespace' => $this->entityMaker->getClassDetails()->getNameSpace(),
            'className' => $this->entityMaker->getClassDetails()->getClassName(),
        ];
    }
}
