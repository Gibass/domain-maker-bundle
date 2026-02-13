<?php

namespace Gibass\DomainMakerBundle\Generator;

use Gibass\DomainMakerBundle\Contracts\MakerInterface;
use Gibass\DomainMakerBundle\Exception\FileAlreadyExistException;
use Symfony\Bundle\MakerBundle\Generator;

class MakerGenerator
{
    private array $operations = [];

    public function __construct(private readonly Generator $generator)
    {
    }

    public function generate(MakerInterface $maker): void
    {
        if ($this->isInPending($maker)) {
            return;
        }

        if (file_exists($maker->getClassDetails()->getFilePath())) {
            throw new FileAlreadyExistException($maker->getClassDetails()->getFilePath());
        }

        $this->generator->generateFile(
            $maker->getClassDetails()->getFilePath(),
            $maker->getTemplate(),
            $maker->getParams(),
        );

        $this->operations[$maker->getClassDetails()->getFullClassName()] = 'generate';
    }

    public function isInPending(MakerInterface $maker): bool
    {
        return isset($this->operations[$maker->getClassDetails()->getFullClassName()]);
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

    public function dumpFile(string $targetPath, string $contents): void
    {
        $this->generator->dumpFile($targetPath, $contents);
    }
}
