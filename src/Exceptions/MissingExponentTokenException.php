<?php


namespace Subit\ExpoSdk\Exceptions;

use Exception;

class MissingExponentTokenException extends Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->message = 'Property \'to\' is required when sending a push notification';
    }
}