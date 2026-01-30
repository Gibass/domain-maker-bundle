<?php

namespace Gibass\DomainMakerBundle\Maker;

use Gibass\DomainMakerBundle\Contracts\MakerChoosableInterface;
use Gibass\DomainMakerBundle\Contracts\MakerInterface;
use Gibass\DomainMakerBundle\Exception\NoDomainException;
use Gibass\DomainMakerBundle\Exception\NoItemToChooseException;
use Gibass\DomainMakerBundle\Generator\ClassDetails;
use Gibass\DomainMakerBundle\Generator\MakerGenerator;
use Gibass\DomainMakerBundle\Manager\DocumentManager;
use Gibass\DomainMakerBundle\Manager\MakerManager;
use Gibass\DomainMakerBundle\Structure\StructureConfig;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker as BaseAbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class AbstractMaker extends BaseAbstractMaker implements MakerInterface
{
    protected ?string $name = null;
    protected ?string $shortName = null;
    protected ?string $domain = null;
    protected ?string $targetPath = null;

    /** @var MakerInterface[] */
    protected array $dependencies = [];

    protected ?ClassDetails $classDetails = null;

    public function __construct(
        protected readonly StructureConfig $config,
        protected readonly MakerManager    $makerManager,
        protected readonly DocumentManager $manager,
        protected readonly MakerGenerator  $generator,
    )
    {
    }

    public function getId(): ?string
    {
        return $this->getNamespace() . '/' . $this->shortName;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function loadArguments(InputInterface $input): void
    {
    }

    public function getParams(): array
    {
        return [];
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    public function needToCreate(): bool
    {
        return true;
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->loadArguments($input);
        $this->createInput($io);
        $this->initialize();
    }

    public function createInput(ConsoleStyle $io): void
    {
        if (!$this->domain) {
            $question = new ConfirmationQuestion('Do You want to create a new domain ?');
            $response = $io->askQuestion($question);

            $question = $response ?
                new Question('Enter the name of your domain :') :
                new ChoiceQuestion('Choose an existing domain :', $this->loadExistingDomains());

            $response = $io->askQuestion($question);
            $this->setDomain($response);
        }

        if (empty($this->domain)) {
            throw new NoDomainException();
        }
    }

    public function getDetails(): ClassDetails
    {
        if ($this->classDetails === null) {
            $this->classDetails = new ClassDetails()
                ->setName($this->name)
                ->setSubPath(str_replace('\\', '/', $this->getSubNameSpace())
            );
        }

        return $this->classDetails;
    }

    public function initialize(): void
    {
        if ($this->classDetails && $this->classDetails->isInitialized()) {
            return;
        }

        $details = $this->generator->createClassDetails($this);

        $this->shortName = $details->getShortName();
        $this->targetPath = $this->getFilePath($this->getDetails()->getSubPath(), $this->shortName);

        $this->classDetails->setInitialized(true);

        foreach ($this->getDependencies() as $dependency) {
            $dependency->initialize();
        }
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        foreach ($this->getDependencies() as $dependency) {
            $this->generator->generate($dependency);
        }

        $this->generator->generate($this);

        $this->generator->write();
        $this->writeSuccessMessage($io);
    }

    public function getTargetPath(): ?string
    {
        return $this->targetPath;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = Str::asCamelCase(trim($domain));

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function loadExistingDomains(): array
    {
        return $this->manager->listDirectories($this->config->getSrcDir());
    }

    protected function getFilePath(string $subPath, string $className): string
    {
        return sprintf('%s/%s/%s.php', $this->getDomainPath(), $subPath, $className);
    }

    protected function getDomainPath(): string
    {
        $domainPath = $this->config->getSrcDir() . $this->domain;

        if (!$this->manager->isDirectoryExist($domainPath)) {
            $this->manager->createDirectory($domainPath);
        }

        return $domainPath;
    }

    protected function getNamespace(): string
    {
        return sprintf('%s\\%s\\%s', $this->config->getRootNamespace(), $this->domain, $this->getSubNameSpace());
    }


    protected function addChoosableMaker(
        ConsoleStyle $io,
        string       $makerClass,
        string       $message,
        array        $choices = ['No', 'Create', 'Choose existing'],
        string       $default = 'No'
    ): void
    {
        $question = new ChoiceQuestion($message, $choices, $default);
        $needMarker = $io->askQuestion($question);

        if ($needMarker === 'No') {
            return;
        }

        $maker = $this->makerManager
            ->getMaker($makerClass)
            ->setDomain($this->domain);

        if ($maker instanceof MakerChoosableInterface) {
            $maker->setChosen($needMarker === 'Choose existing');
        }

        if ($maker instanceof MakerChoosableInterface && $maker->isChosen()) {
            $maker->chooseInput($io);
        } else {
            $maker->createInput($io);
        }

        $this->dependencies[$makerClass] = $maker;
    }
}
