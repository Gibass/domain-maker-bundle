<?php

namespace Gibass\DomainMakerBundle\Generator;

use Gibass\DomainMakerBundle\Contracts\MakerInterface;
use Gibass\DomainMakerBundle\Exception\FileAlreadyExistException;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

class MakerGenerator
{
    private array $operations = [];

    public function __construct(private readonly Generator $generator)
    {
    }

    public function generate(MakerInterface $maker): void
    {
        if (!$maker->needToCreate() || $this->isInPending($maker)) {
            return;
        }

        if (file_exists($maker->getTargetPath())) {
            throw new FileAlreadyExistException($maker->getTargetPath());
        }

        $this->generator->generateFile(
            $maker->getTargetPath(),
            $maker->getTemplate(),
            $maker->getParams(),
        );

        $this->operations[$maker->getId()] = 'generate';
    }

    public function isInPending(MakerInterface $maker): bool
    {
        return isset($this->operations[$maker->getId()]);
    }

    public function write(): void
    {
        $this->generator->writeChanges();
        $this->clearOperations();
    }

    private function clearOperations(): void
    {
        $this->operations = [];
    }

    public function createClassDetails(MakerInterface $maker): ClassNameDetails
    {
        return $this->generator->createClassNameDetails(
            $maker->getDetails()->getName(),
            $maker->getSubNameSpace(),
            $maker->getDetails()->getSuffix()
        );
    }
}
