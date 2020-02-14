<?php


namespace Subit\ExpoSdk\Exceptions;

use Exception;

class NoExpoMessageReceiptException extends Exception
{
    public function __construct()
    {
        parent::__construct();

        $this->message = 'Array of ExpoMessageReceipt ids is empty';
    }
}