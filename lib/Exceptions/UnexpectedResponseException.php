<?php

namespace ExponentPhpSDK\Exceptions;

class UnexpectedResponseException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Unexpected response from server');
    }
}
