<?php


namespace Subit\ExpoSdk\Exceptions;

use Exception;

class NotAValidPriorityException extends Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->message = 'See Priority class for possibilities';
    }
}