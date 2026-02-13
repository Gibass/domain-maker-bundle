<?php

namespace Gibass\DomainMakerBundle\Maker;

use Gibass\DomainMakerBundle\Builder\DependencyBuilder;
use Gibass\DomainMakerBundle\Contracts\DependencyInterface;
use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Gibass\DomainMakerBundle\Maker\Abstract\AbstractMaker;
use Gibass\DomainMakerBundle\Trait\DependencyMakerTrait;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class GatewayMaker extends AbstractMaker implements DependencyInterface
{
    use DependencyMakerTrait;

    public static function getCommandName(): string
    {
        return 'maker:gateway';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Create a new gateway.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Choose a name for your gateway')
        ;
    }

    public function interactInput(ConsoleStyle $io): void
    {
        $this->name = $this->name ?? $io->ask('Enter the new gateway name: ');
    }

    public function createClassDetails(): ClassDetails
    {
        return ClassDetails::create(
            name: $this->name,
            namespace: $this->getDomainNamespace() . '\\Domain\\Gateway',
            targetPath: $this->getDomainPath() . '/Domain/Gateway',
            suffix: 'GatewayInterface',
        );
    }

    public function getBuilderClass(): array
    {
        return [
            DependencyBuilder::class
        ];
    }

    public function getChosenDirectory(): string
    {
        return $this->config->getSrcDir() . $this->domain . '/Domain/Gateway/';
    }

    public function getTemplate(): string
    {
        return $this->config->getTemplate('gateway/gateway');
    }

    public function getParams(): array
    {
        return [
            'namespace' => $this->getClassDetails()->getNamespace(),
            'className' => $this->getClassDetails()->getClassName(),
        ];
    }
}
