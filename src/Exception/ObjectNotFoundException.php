<?php

namespace App\Exception;

class ObjectNotFoundException extends \Exception
{
    public function __construct(string $message = 'Object not found', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}