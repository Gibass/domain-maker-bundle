<?php

namespace Gibass\DomainMakerBundle\Manager;

use Gibass\DomainMakerBundle\Builder\Registry\BuilderRegistry;
use Gibass\DomainMakerBundle\Contracts\ChoosableMakerInterface;
use Gibass\DomainMakerBundle\Contracts\DependencyInterface;
use Gibass\DomainMakerBundle\Contracts\MakerInterface;
use Gibass\DomainMakerBundle\Enum\ChoicesAction;
use Gibass\DomainMakerBundle\Enum\DependencyType;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Component\Console\Input\InputInterface;

readonly class DependencyManager
{
    public function __construct(private MakerManager $makerManager, private BuilderRegistry $builderRegistry)
    {
    }

    public function interactOptional(
        InputInterface $input,
        ConsoleStyle $io,
        string $makerClass,
        string $message,
        string $domain,
        ChoicesAction $default = ChoicesAction::create
    ): ?MakerInterface
    {
        $needMarker = $io->choice($message, ChoicesAction::getChoicesAction(DependencyType::optional), $default->value);
        if ($needMarker === ChoicesAction::no->value) {
            return null;
        }

        return $this->createChoosableMaker($input, $io, $makerClass, $domain, $needMarker);
    }

    public function interactRequired(
        InputInterface $input,
        ConsoleStyle $io,
        string $makerClass,
        string $message,
        string $domain,
        ChoicesAction $default = ChoicesAction::create
    ): ?MakerInterface
    {
        $action = $io->choice($message, ChoicesAction::getChoicesAction(DependencyType::required), $default->value);

        return $this->createChoosableMaker($input, $io, $makerClass, $domain, $action);
    }

    public function autoCreate(
        InputInterface $input,
        ConsoleStyle $io,
        string $makerClass,
        string $name,
        string $domain
    ): ?MakerInterface
    {
        $maker = $this->makerManager->getMaker($makerClass)
            ->setDomain($domain)
            ->setName($name)
        ;

        if (!$maker instanceof DependencyInterface) {
            return null;
        }

        $maker->setAsDependency();
        $maker->setClassDetails($maker->createClassDetails());

        foreach ($maker->getBuilderClass() as $name) {
            if ($builder = $this->builderRegistry->getBuilder($name)) {
                $builder->interact($maker, $input, $io);
            }
        }

        if (file_exists($maker->getClassDetails()->getFilePath())) {
            $maker->setNeedToCreate(false);
        }

        return $maker;
    }

    private function createChoosableMaker(InputInterface $input, ConsoleStyle $io, string $makerClass, string $domain, string $action): ?MakerInterface
    {
        $maker = $this->makerManager->getMaker($makerClass)->setDomain($domain);

        if (!$maker instanceof ChoosableMakerInterface) {
            return null;
        }

        $maker->setAsDependency();
        $maker->setChosen($action === ChoicesAction::choose->value);
        $maker->setNeedToCreate($action === ChoicesAction::create->value);

        foreach ($maker->getBuilderClass() as $name) {
            if ($builder = $this->builderRegistry->getBuilder($name)) {
                $builder->interact($maker, $input, $io);
            }
        }

        $maker->setClassDetails($maker->createClassDetails());

        return $maker;
    }
}
