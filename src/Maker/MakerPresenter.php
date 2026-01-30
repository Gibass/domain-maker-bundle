<?php

namespace Gibass\DomainMakerBundle\Maker;

use Gibass\DomainMakerBundle\Contracts\MakerChoosableInterface;
use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Gibass\DomainMakerBundle\Trait\MarkerChoosableTrait;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MakerPresenter extends AbstractMaker implements MakerChoosableInterface
{
    use MarkerChoosableTrait;

    private ?string $type = null;

    public static function getCommandName(): string
    {
        return 'maker:presenter';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->setDescription('Create a new presenter.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Choose a name for your new presenter')
        ;
    }

    public function getSubNameSpace(): string
    {
        return 'UserInterface\\Presenter\\' . ucfirst($this->type);
    }

    public function getTemplate(): string
    {
        return $this->config->getTemplate('presenter/presenter_' . strtolower($this->type), 'presenter');
    }

    public function loadArguments(InputInterface $input): void
    {
        $this->name = $input->getArgument('name');
    }

    public function chooseInput(ConsoleStyle $io): void
    {
        $question = new ChoiceQuestion('Choose a type for your presenter:', ['Html', 'Json']);
        $this->type = $io->askQuestion($question);

        $question = new ChoiceQuestion('Choose an existing presenter :', $this->loadExistingItems($this->getDomainPath() . '/UserInterface/Presenter/' . $this->type));
        $this->name = $io->askQuestion($question);
    }

    public function createInput(ConsoleStyle $io): void
    {
        parent::createInput($io);

        $question = new ChoiceQuestion('Choose a type for your presenter:', ['Html', 'Json']);
        $this->type = $io->askQuestion($question);

        if (!$this->name) {
            $question = new Question('Create a name for your new presenter');
            $this->name = $io->askQuestion($question);
        }
    }

    public function getDetails(): ClassDetails
    {
        return parent::getDetails()->setSuffix('Presenter' . strtoupper($this->type));
    }

    public function create(Generator $generator): void
    {
        $generator->generateFile(
            $this->targetPath,
            $this->config->getTemplate('presenter/presenter_' . strtolower($this->type), 'presenter'),
            $this->getParams()
        );
    }

    public function getParams(): array
    {
        $useStatements = new UseStatementGenerator([
            $this->type == 'Json' ? JsonResponse::class : Response::class,
        ]);

        return [
            'namespace' => $this->getNamespace(),
            'className' => $this->shortName,
            'rootNamespace' => $this->config->getRootNamespace(),
            'useStatements' => $useStatements,
            'response' => $this->type == 'Json' ? 'JsonResponse' : 'Response',
            'template' => 'pages/'.Str::asCommand($this->domain).'/index/index.html.twig'
        ];
    }
}
