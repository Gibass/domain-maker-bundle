<?php

namespace Gibass\DomainMakerBundle\Exception;

class FileAlreadyExistException extends \RuntimeException
{
    public function __construct(string $path, int $code = 0, \Throwable $previous = null)
    {
        $message = 'The file "' . $path . '" is already exist.';

        parent::__construct($message, $code, $previous);
    }
}
