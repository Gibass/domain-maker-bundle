<?php

namespace Gibass\DomainMakerBundle\Maker;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;

class MakerRepository extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'maker:repository';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Create a new repository.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Choose a name for your repository')
        ;
    }

    public function loadArguments(InputInterface $input): void
    {
        $this->name = $input->getArgument('name');
    }

    public function getSubNameSpace(): string
    {
        return 'Infrastructure\\Adapter\\Repository';
    }

    public function getTemplate(): string
    {
        return $this->config->getTemplate('repository/repository');
    }

    public function createInput(ConsoleStyle $io): void
    {
        parent::createInput($io);

        if (!$this->name) {
            $question = new Question('Create a name for your new repository:');
            $this->name = $io->askQuestion($question);
        }

        $this->addGateway();

        if (empty($this->dependencies[MakerEntity::class])) {
            $this->addChoosableMaker(
                $io,
                MakerEntity::class,
                'Create or choose an Entity for the repository',
                ['Create', 'Choose existing'],
                'Choose existing'
            );

            $this->dependencies[MakerEntity::class] instanceof MakerEntity &&
            $this->dependencies[MakerEntity::class]->setRepository($this);
        }
    }

    public function initialize(): void
    {
        parent::initialize();

        if (!empty($this->dependencies[MakerGateway::class])
            && $this->dependencies[MakerGateway::class] instanceof MakerGateway
            && file_exists($this->dependencies[MakerGateway::class]->getTargetPath())
        ) {
            $this->dependencies[MakerGateway::class]->setChosen(true);
        }
    }

    public function getDetails(): ClassDetails
    {
        return parent::getDetails()->setName($this->name)->setSuffix('Repository');
    }

    public function getParams(): array
    {
        $useStatements = new UseStatementGenerator([
            ServiceEntityRepository::class,
            ManagerRegistry::class
        ]);

        return [
            'namespace' => $this->getNameSpace(),
            'className' => $this->getShortName(),
            'rootNamespace' => $this->config->getRootNamespace(),
            'useStatements' => $useStatements,
            'gateway' => !empty($this->dependencies[MakerGateway::class]) ? $this->dependencies[MakerGateway::class]->getParams() : [],
            'entity' => !empty($this->dependencies[MakerEntity::class]) ? [
                'namespace' => $this->dependencies[MakerEntity::class]->getNameSpace(),
                'className' => $this->dependencies[MakerEntity::class]->getShortName(),
            ] : []
        ];
    }

    public function setEntity(MakerEntity $entityMaker): void
    {
        $this->dependencies[MakerEntity::class] = $entityMaker;
    }

    private function addGateway(): void
    {
        $this->dependencies[MakerGateway::class] = $this->makerManager
            ->getMaker(MakerGateway::class)
            ->setDomain($this->domain)
            ->setName($this->name)
        ;
    }
}
