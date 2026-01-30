<?php

namespace Gibass\DomainMakerBundle\Maker;

use Gibass\DomainMakerBundle\Contracts\MakerChoosableInterface;
use Gibass\DomainMakerBundle\Contracts\MakerConfigurableInterface;
use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Gibass\DomainMakerBundle\Generator\MakerGenerator;
use Gibass\DomainMakerBundle\Manager\DocumentManager;
use Gibass\DomainMakerBundle\Manager\MakerManager;
use Gibass\DomainMakerBundle\Structure\StructureConfig;
use Gibass\DomainMakerBundle\Trait\MakerConfigurableTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Yaml\Yaml;

class MakerController extends AbstractMaker implements MakerConfigurableInterface
{
    use MakerConfigurableTrait;

    public static function getCommandName(): string
    {
        return 'maker:controller';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Create a new controller.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Choose a name for your new controller')
        ;
    }

    public function getSubNameSpace(): string
    {
        return 'UserInterface\\Controller';
    }

    public function getTemplate(): string
    {
        return $this->config->getTemplate('controller/' . $this->loadTemplate());
    }

    public function loadArguments(InputInterface $input): void
    {
        $this->name = $input->getArgument('name');
    }

    public function createInput(ConsoleStyle $io): void
    {
        parent::createInput($io);

        if (!$this->name) {
            $question = new Question('Create a name for your new Controller:');
            $this->name = $io->askQuestion($question);
        }

        $this->addChoosableMaker($io, MakerUseCase::class, 'Do You want a UseCase for the controller ?');
        $this->addChoosableMaker($io, MakerPresenter::class, 'Do You want a presenter for the controller ?');
    }

    public function getDetails(): ClassDetails
    {
        return parent::getDetails()->setSuffix('Controller');
    }

    public function initConfigs(): void
    {
        $this->configGenerator->addConfig('routes.yaml', $this->buildRouteConfig(...));
    }

    public function buildRouteConfig(array $contents): array
    {
        $key = Str::asLowerCamelCase($this->domain) . '.controller';

        if (isset($contents[$key])) {
            return $contents;
        }

        $contents[$key] =  [
            'resource' => [
                'path' => '../src/'. $this->domain .'/UserInterface/Controller/',
                'namespace' => $this->getNamespace(),
            ],
            'type' => 'attribute'
        ];

        return $contents;
    }

    public function getParams(): array
    {
        $useStatements = new UseStatementGenerator([
            AbstractController::class,
            Route::class,
        ]);

        return [
            'namespace' => $this->getNamespace(),
            'className' => $this->shortName,
            'useStatements' => $useStatements,
            'rootNamespace' => $this->config->getRootNamespace(),
            'routeName' => Str::asRouteName($this->name),
            'routePath' => Str::asRoutePath($this->name),
            'method' => Str::asLowerCamelCase($this->name),
            'presenter' => isset($this->dependencies[MakerPresenter::class]) ? $this->dependencies[MakerPresenter::class]->getParams() : [],
            'useCase' => isset($this->dependencies[MakerUseCase::class]) ? $this->dependencies[MakerUseCase::class]->getParams() : [],
        ];
    }

    private function loadTemplate(): string
    {
        if (isset($this->dependencies[MakerUseCase::class]) && isset($this->dependencies[MakerPresenter::class])) {
            return 'controller_useCase_presenter';
        }

        if (isset($this->dependencies[MakerUseCase::class])) {
            return 'controller_useCase';
        }

        if (isset($this->dependencies[MakerPresenter::class])) {
            return 'controller_presenter';
        }

        return 'controller';
    }
}
