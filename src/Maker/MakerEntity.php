<?php

namespace Gibass\DomainMakerBundle\Maker;

use Gibass\DomainMakerBundle\Contracts\MakerChoosableInterface;
use Gibass\DomainMakerBundle\Trait\MarkerChoosableTrait;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class MakerEntity extends AbstractMaker implements MakerChoosableInterface
{
    use MarkerChoosableTrait;

    private AbstractMaker $repositoryMaker;

    public static function getCommandName(): string
    {
        return 'maker:entity';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Create a new entity.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Choose a name for your entity')
        ;
    }

    public function loadArguments(InputInterface $input): void
    {
        $this->name = $input->getArgument('name');
    }

    public function getSubNameSpace(): string
    {
        return 'Domain\\Model\\Entity';
    }

    public function getTemplate(): string
    {
        return $this->config->getTemplate('entity/entity');
    }

    public function createInput(ConsoleStyle $io): void
    {
        parent::createInput($io);

        if (!$this->name) {
            $question = new Question('Create a name for your new entity:');
            $this->name = $io->askQuestion($question);
        }

        if (empty($this->dependencies[MakerRepository::class])) {
            $this->addRepository($io);
        }
    }

    public function chooseInput(ConsoleStyle $io): void
    {
        $question = new ChoiceQuestion('Choose an existing Entity :', $this->loadExistingItems($this->getDomainPath() . '/Domain/Model/Entity'));
        $this->name = $io->askQuestion($question);
    }

    public function getParams(): array
    {
        return [
            'namespace' => $this->getNamespace(),
            'className' => $this->shortName,
            'rootNamespace' => $this->config->getRootNamespace(),
            'repository' => $this->dependencies[MakerRepository::class]->getParams(),
        ];
    }

    public function setRepository(MakerRepository $repositoryMaker): void
    {
        $this->dependencies[MakerRepository::class] = $repositoryMaker;
    }

    private function addRepository(ConsoleStyle $io): void
    {
        $this->dependencies[MakerRepository::class] = $this->makerManager
            ->getMaker(MakerRepository::class)
            ->setDomain($this->domain)
            ->setName($this->name)
        ;

        if ($this->dependencies[MakerRepository::class] instanceof MakerRepository) {
            $this->dependencies[MakerRepository::class]->setEntity($this);
        }

        $this->dependencies[MakerRepository::class]->createInput($io);
    }
}
