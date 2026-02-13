<?php

namespace Gibass\DomainMakerBundle\Maker;

use Gibass\Domain\Core\UserInterface\Presenter\AbstractJsonPresenter;
use Gibass\Domain\Core\UserInterface\Presenter\AbstractWebPresenter;
use Gibass\DomainMakerBundle\Builder\ChoosableBuilder;
use Gibass\DomainMakerBundle\Contracts\ChoosableMakerInterface;
use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Gibass\DomainMakerBundle\Maker\Abstract\AbstractMaker;
use Gibass\DomainMakerBundle\Trait\ChoosableMakerTrait;
use Gibass\DomainMakerBundle\Trait\DependencyMakerTrait;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PresenterMaker extends AbstractMaker implements ChoosableMakerInterface
{
    use DependencyMakerTrait;
    use ChoosableMakerTrait;

    public const TYPES = ['Html', 'Json'];
    public const DEFAULT_TYPE = 'Json';

    private ?string $type = null;

    public static function getCommandName(): string
    {
        return 'maker:presenter';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Create a new presenter.')
            ->addArgument('name', InputArgument::REQUIRED, 'Choose a name for your new presenter')
        ;
    }

    public function interactInput(ConsoleStyle $io): void
    {
        $this->name = $this->name ??
            $this->isChosen ?
            $io->choice('Choose an existing presenter:', $this->loadExistingItems()) :
            $io->ask('Enter the new presenter name:');
    }

    public function interactOptions(ConsoleStyle $io): void
    {
        $this->type = $io->choice('Choose a type for your presenter:', self::TYPES, self::DEFAULT_TYPE);
    }

    public function createClassDetails(): ClassDetails
    {
        return ClassDetails::create(
            name: $this->name,
            namespace: $this->getDomainNamespace() . '\\UserInterface\\Presenter\\' . ucfirst($this->type),
            targetPath: $this->getDomainPath() . '/UserInterface/Presenter/' . ucfirst($this->type),
            suffix: 'Presenter' . strtoupper($this->type),
            extendsClass: match ($this->type) { 'Html' => AbstractWebPresenter::class, 'Json' => AbstractJsonPresenter::class },
            useStatements: match ($this->type) {
                'Html' => [AbstractWebPresenter::class, Response::class],
                'Json' => [AbstractJsonPresenter::class, JsonResponse::class]
            }
        );
    }

    public function getBuilderClass(): array
    {
        return [
            ChoosableBuilder::class
        ];
    }

    public function getChosenDirectory(): string
    {
        return $this->config->getSrcDir() . $this->domain . '/UserInterface/Presenter/' . ucfirst($this->type) . '/';
    }

    public function getTemplate(): string
    {
        return $this->config->getTemplate('presenter/presenter_' . strtolower($this->type), 'presenter');
    }

    public function getParams(): array
    {
        return [
            'namespace' => $this->getClassDetails()->getNamespace(),
            'className' => $this->getClassDetails()->getClassName(),
            'rootNamespace' => $this->config->getRootNamespace(),
            'useStatements' => $this->getClassDetails()->getUseStatementGenerator(),
            'response' => $this->type == 'Json' ? 'JsonResponse' : 'Response',
            'template' => 'pages/'.Str::asCommand($this->getDomain()).'/index/index.html.twig'
        ];
    }
}
