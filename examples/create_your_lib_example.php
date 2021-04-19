<?php namespace MyNameSpace;


class MyClient extends \PettyRest\Client
{
    /**
     * @param MyApiRequest_A $r
     * @return MyResult_A
     * @throws MyApiError
     */
    public function getResult_A( MyApiRequest_A $r ): MyResult_A
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->sendRequest($r);
    }

    protected function getExceptionClass(\Throwable $e): string
    {
        return MyApiError::class;
    }
}

// example 1

class MyApiRequest_A extends \PettyRest\Request
{
    public $event;
    public $data = [
        'name' => null,
        'name2' => null,
    ];
    public $returnAllResults;

    public function __construct()
    {
        parent::__construct('/api-method-a/');
    }

    public function getResponseDummy(): \PettyRest\Response
    {
        return new MyRequestResult();
    }
}

class MyResult_A extends \PettyRest\Response
{
    public $status;
    public $fieldA;
}

class MyRequestResult extends \PettyRest\Response
{
    public $returnData = [
        'field1' => null,
        'field2' => null,
    ];
}

class MyApiError extends \PettyRest\ApiException {}