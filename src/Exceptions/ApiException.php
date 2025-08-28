<?php

namespace CjDropshipping\Exceptions;

class ApiException extends \Exception
{
    protected $response;

    public function __construct($message = "", $code = 0, $response = null, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}