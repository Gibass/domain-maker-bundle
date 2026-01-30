<?php

namespace Gibass\DomainMakerBundle\Exception;

use Throwable;

class NoDomainException extends \RuntimeException
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        $message = 'You must specify a domain to continue.';

        parent::__construct($message, $code, $previous);
    }
}
