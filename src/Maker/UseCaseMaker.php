<?php

namespace Gibass\DomainMakerBundle\Maker;

use Gibass\DomainMakerBundle\Builder\ChoosableBuilder;
use Gibass\DomainMakerBundle\Contracts\ChoosableMakerInterface;
use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Gibass\DomainMakerBundle\Maker\Abstract\AbstractMaker;
use Gibass\DomainMakerBundle\Trait\ChoosableMakerTrait;
use Gibass\DomainMakerBundle\Trait\DependencyMakerTrait;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class UseCaseMaker extends AbstractMaker implements ChoosableMakerInterface
{
    use DependencyMakerTrait;
    use ChoosableMakerTrait;

    public static function getCommandName(): string
    {
        return 'maker:use-case';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Create a new use case.')
            ->addArgument('name', InputArgument::REQUIRED, 'Choose a name for your new useCase')
        ;
    }

    public function interactInput(ConsoleStyle $io): void
    {
        $this->name = $this->name ??
            $this->isChosen ?
            $io->choice('Choose an existing useCase:', $this->loadExistingItems()) :
            $io->ask('Enter the new useCase name: ');
    }

    public function createClassDetails(): ClassDetails
    {
        return ClassDetails::create(
            name: $this->name,
            namespace: $this->getDomainNamespace() . '\\Domain\\UseCase',
            targetPath: $this->getDomainPath() . '/Domain/UseCase',
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
        return $this->config->getSrcDir() . $this->domain . '/Domain/UseCase/';
    }


    public function getTemplate(): string
    {
        return $this->config->getTemplate('use_case');
    }

    public function getParams(): array
    {
        return [
            'namespace' => $this->classDetails->getNamespace(),
            'className' => $this->classDetails->getClassName()
        ];
    }
}
