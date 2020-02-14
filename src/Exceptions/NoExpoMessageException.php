<?php


namespace Subit\ExpoSdk\Exceptions;

use Exception;

class NoExpoMessageException extends Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->message = 'No ExpoMessage in passed argument';
    }
}