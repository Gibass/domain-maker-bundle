<?php

namespace Gibass\DomainMakerBundle\Generator;

use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;

readonly class ClassDetails
{
    public function __construct(
        private string $className,
        private string $suffix,
        private string $namespace,
        private string $filePath,
        private UseStatementGenerator $useStatementGenerator,
    )
    {
    }

    public static function create(string $name, ?string $namespace, string $targetPath,  ?string $suffix = null, ?string $extendsClass = null, array $useStatements = []): self
    {
        $className = Str::asClassName($name, $suffix ?? '');

        $useStatements = new UseStatementGenerator($useStatements);

        if ($extendsClass) {
            $useStatements->addUseStatement($extendsClass);
        }

        $filePath = sprintf('%s/%s.php', $targetPath, $className);

        return new self(
            className: $className,
            suffix: $suffix ?? '',
            namespace: $namespace,
            filePath: $filePath,
            useStatementGenerator: $useStatements
        );
    }

    public function getClassName(bool $withSuffix = true): string
    {
        return $withSuffix ? $this->className : Str::removeSuffix($this->className, $this->suffix);
    }

    public function getFullClassName(): string
    {
        return $this->namespace . '\\' . $this->className;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getUseStatementGenerator(): UseStatementGenerator
    {
        return $this->useStatementGenerator;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
