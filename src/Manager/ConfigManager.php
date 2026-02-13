<?php

namespace Gibass\DomainMakerBundle\Manager;

use Gibass\DomainMakerBundle\Structure\StructureConfig;
use Symfony\Component\Yaml\Yaml;

class ConfigManager
{
    private array $configContents = [];

    public function __construct(private readonly StructureConfig $config)
    {
    }

    public function addConfig(string $configFilename, callable $contentGenerator): void
    {
        $key = $this->config->getConfigDir() . $configFilename;
        $this->configContents[$key] = $contentGenerator($this->loadConfigContent($key));
    }

    public function getConfigContents(): array
    {
        return $this->configContents;
    }

    private function loadConfigContent(string $key): array
    {
        return $this->configContents[$key] ?? Yaml::parse(file_get_contents($key)) ?? [];
    }
}
