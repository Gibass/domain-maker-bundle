<?php

namespace Gibass\DomainMakerBundle\Maker;

use Gibass\DomainMakerBundle\Builder\ConfigurableBuilder;
use Gibass\DomainMakerBundle\Builder\HasDependencyBuilder;
use Gibass\DomainMakerBundle\Builder\Registry\BuilderRegistry;
use Gibass\DomainMakerBundle\Contracts\ConfigurableMakerInterface;
use Gibass\DomainMakerBundle\Contracts\HasDependencyMakerInterface;
use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Gibass\DomainMakerBundle\Generator\MakerGenerator;
use Gibass\DomainMakerBundle\Maker\Abstract\AbstractMaker;
use Gibass\DomainMakerBundle\Manager\DependencyManager;
use Gibass\DomainMakerBundle\Manager\ConfigManager;
use Gibass\DomainMakerBundle\Manager\DocumentManager;
use Gibass\DomainMakerBundle\Structure\StructureConfig;
use Gibass\DomainMakerBundle\Trait\ConfigurableMakerTrait;
use Gibass\DomainMakerBundle\Trait\HasDependencyMakerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Routing\Attribute\Route;

class ControllerMaker extends AbstractMaker implements ConfigurableMakerInterface, HasDependencyMakerInterface
{
    use ConfigurableMakerTrait;
    use HasDependencyMakerTrait;

    public function __construct(
        StructureConfig                    $config,
        DocumentManager                    $docManager,
        BuilderRegistry                    $builderRegistry,
        MakerGenerator                     $generator,
        private readonly ConfigManager     $configManager,
        private readonly DependencyManager $dependencyManager
    )
    {
        parent::__construct($config, $docManager, $builderRegistry, $generator);
    }

    public static function getCommandName(): string
    {
        return 'maker:controller';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Create a new controller.')
            ->addArgument('name', InputArgument::REQUIRED, 'Choose a name for your new controller');
    }

    public function interactDependencies(InputInterface $input, ConsoleStyle $io): void
    {
        $this->addDependency(
            $this->dependencyManager->interactOptional(
                input: $input,
                io: $io,
                makerClass: UseCaseMaker::class,
                message: 'Do You want a UseCase for the controller ?',
                domain: $this->domain
            )
        );

        $this->addDependency(
            $this->dependencyManager->interactOptional(
                input: $input,
                io: $io,
                makerClass: PresenterMaker::class,
                message: 'Do You want a presenter for the controller ?',
                domain: $this->domain
            )
        );
    }

    public function createClassDetails(): ClassDetails
    {
        return ClassDetails::create(
            name: $this->name,
            namespace: $this->getDomainNamespace() . '\\UserInterface\\Controller',
            targetPath: $this->getDomainPath() . '/UserInterface/Controller',
            suffix: 'Controller',
            useStatements: [AbstractController::class, Route::class],
        );
    }

    public function getBuilderClass(): array
    {
        return [
            HasDependencyBuilder::class,
            ConfigurableBuilder::class
        ];
    }

    public function getTemplate(): string
    {
        return $this->config->getTemplate('controller/' . $this->loadTemplate());
    }

    public function initConfigs(): void
    {
        $this->configManager->addConfig('routes.yaml', $this->buildRouteConfig(...));
    }

    public function buildRouteConfig(array $contents): array
    {
        $key = Str::asLowerCamelCase($this->getDomain()) . '.controller';

        if (isset($contents[$key])) {
            return $contents;
        }

        $contents[$key] = [
            'resource' => [
                'path' => '../src/' . $this->getDomain() . '/UserInterface/Controller/',
                'namespace' => $this->getClassDetails()->getNamespace(),
            ],
            'type' => 'attribute'
        ];

        return $contents;
    }

    public function getParams(): array
    {
        return [
            'namespace' => $this->getClassDetails()->getNamespace(),
            'className' => $this->getClassDetails()->getClassName(),
            'useStatements' => $this->getClassDetails()->getUseStatementGenerator(),
            'rootNamespace' => $this->config->getRootNamespace(),
            'routeName' => Str::asRouteName($this->getClassDetails()->getClassName(false)),
            'routePath' => Str::asRoutePath($this->getClassDetails()->getClassName(false)),
            'method' => Str::asLowerCamelCase($this->getClassDetails()->getClassName(false)),
            'presenter' => isset($this->dependencies[PresenterMaker::class]) ? $this->dependencies[PresenterMaker::class]->getParams() : [],
            'useCase' => isset($this->dependencies[UseCaseMaker::class]) ? $this->dependencies[UseCaseMaker::class]->getParams() : [],
        ];
    }

    private function loadTemplate(): string
    {
        if (isset($this->dependencies[UseCaseMaker::class]) && isset($this->dependencies[PresenterMaker::class])) {
            return 'controller_useCase_presenter';
        }

        if (isset($this->dependencies[UseCaseMaker::class])) {
            return 'controller_useCase';
        }

        if (isset($this->dependencies[PresenterMaker::class])) {
            return 'controller_presenter';
        }

        return 'controller';
    }
}
