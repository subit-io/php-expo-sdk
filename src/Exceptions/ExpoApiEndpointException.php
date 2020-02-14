<?php


namespace Subit\ExpoSdk\Exceptions;


class ExpoApiEndpointException extends \Exception
{
    public function __construct($errors)
    {
        parent::__construct();

        $this->message = "An error occurred with the entire request to Expos API endpoint. API response error:\n" . json_encode($errors, JSON_PRETTY_PRINT);
    }
}