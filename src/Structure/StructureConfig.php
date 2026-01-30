<?php

namespace Gibass\DomainMakerBundle\Structure;

readonly class StructureConfig
{
    public function __construct(
        private string $rootNamespace,
        private string $configDir,
        private string $srcDir,
    ) {
    }

    public function getRootNamespace(): string
    {
        return $this->rootNamespace;
    }

    public function getSrcDir(): string
    {
        return $this->srcDir;
    }

    public function getConfigDir(): string
    {
        return $this->configDir;
    }

    public function getTemplate(string $key, ?string $default = null): string
    {
        $template =  __DIR__ . "/../Resources/skeleton/{$key}.tpl.php";

        if (!file_exists($template) && $default) {
            return __DIR__ . "/../Resources/skeleton/{$default}.tpl.php";
        }

        return $template;
    }
}
