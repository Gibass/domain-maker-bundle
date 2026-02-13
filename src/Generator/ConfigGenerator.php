<?php

namespace Gibass\DomainMakerBundle\Generator;

use Gibass\DomainMakerBundle\Structure\StructureConfig;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Component\Yaml\Yaml;

class ConfigGenerator
{
    private array $configContents = [];

    public function __construct(private readonly StructureConfig $config, private readonly Generator $generator)
    {
    }

    public function addConfig(string $configFilename, callable $contentGenerator): void
    {
        $key = $this->config->getConfigDir() . $configFilename;
        $this->configContents[$key] = $contentGenerator($this->loadConfigContent($key));
    }

    public function loadConfigContent(string $key): array
    {
        return $this->configContents[$key] ?? Yaml::parse(file_get_contents($key)) ?? [];
    }

    public function generate(): void
    {
        foreach ($this->configContents as $key => $configContent) {
            $this->generator->dumpFile($key, Yaml::dump($configContent));
        }
    }
}
