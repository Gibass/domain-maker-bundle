<?php

namespace Gibass\DomainMakerBundle\Test\Helper\Provider;

class MakerTestContent
{
    private array $contents = [];

    public function __construct(private readonly string $domainName)
    {
    }

    public function getDomain(): string
    {
        return $this->domainName;
    }

    public function getContents(): array
    {
        return $this->contents;
    }

    public function addContent(string $type, string $filename, string $content): self
    {
        $this->contents[] = [
            'type' => $type,
            'filename' => $filename,
            'content' => $content
        ];

        return $this;
    }
}
