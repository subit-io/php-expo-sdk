<?php


namespace Subit\ExpoSdk\Exceptions;

use Exception;

class WrongReceiptIdException extends Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->message = 'None-existing receipt id';
    }
}