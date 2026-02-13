<?php

namespace Gibass\DomainMakerBundle\Exception;

class InvalidMakerException extends \RuntimeException
{
    public function __construct(string $class, int $code = 0, ?\Throwable $previous = null)
    {
        $message = sprintf('The maker must be implement "%s" interface.', $class);

        parent::__construct($message, $code, $previous);
    }
}
