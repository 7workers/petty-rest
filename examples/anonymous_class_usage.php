<?php namespace SomeNameSpace;

use PettyRest\Client;

$c = new class() extends Client{

    protected function getExceptionClass(\Throwable $e): string
    {
        // TODO: Implement getExceptionClass() method.
    }
};