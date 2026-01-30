<?php

namespace Gibass\DomainMakerBundle\Test\Helper\Provider;

class MakerTestFailed extends MakerTestGenerate
{
    private string $exception;
    private readonly string $message;

    public function getException(): string
    {
        return $this->exception;
    }

    public function setException(string $exception, string $message = ''): self
    {
        $this->exception = $exception;

        if ($message) {
            $this->message = $message;
        }

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
