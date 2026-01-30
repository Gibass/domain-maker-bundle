<?php

namespace Gibass\DomainMakerBundle\Maker;

use Gibass\DomainMakerBundle\Contracts\MakerChoosableInterface;
use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Gibass\DomainMakerBundle\Trait\MarkerChoosableTrait;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;

class MakerGateway extends AbstractMaker implements MakerChoosableInterface
{
    use MarkerChoosableTrait;

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

    public function loadArguments(InputInterface $input): void
    {
        $this->name = $input->getArgument('name');
    }

    public function getSubNameSpace(): string
    {
        return 'Domain\\Gateway';
    }

    public function getTemplate(): string
    {
        return $this->config->getTemplate('gateway/gateway');
    }

    public function createInput(ConsoleStyle $io): void
    {
        parent::createInput($io);

        if (!$this->name) {
            $question = new Question('Create a name for your new gateway:');
            $this->name = $io->askQuestion($question);
        }
    }

    public function chooseInput(ConsoleStyle $io): void
    {

    }

    public function getDetails(): ClassDetails
    {
        return parent::getDetails()->setSuffix('GatewayInterface');
    }

    public function getParams(): array
    {
        return [
            'namespace' => $this->getNameSpace(),
            'className' => $this->shortName
        ];
    }
}
