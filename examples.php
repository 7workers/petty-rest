<?php /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */ /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace MyNameSpace;

use PettyRest\ApiException;
use PettyRest\Client;
use PettyRest\Request;
use PettyRest\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

require 'src/Client.php';
require 'src/ApiException.php';
require 'src/Request.php';
require 'src/Response.php';

class MyApiError extends \PettyRest\ApiException {}

// example 1

class MyApiRequest extends \PettyRest\Request
{
    public $event;
    public $data = [
        'name' => null,
        'name2' => null,
    ];

    public function __construct()
    {
        parent::__construct('/api-method-a/');
    }

    public function getResponseDummy(): Response
    {
        return new MyRequestResult();
    }
}

class MyRequestResult extends \PettyRest\Response
{
    public $returnData = [
        'field1' => null,
        'field2' => null,
    ];

    protected function getExceptionObject($stringOrException): \Throwable
    {
        if( $stringOrException instanceof \Throwable ) return new MyApiError($stringOrException->getMessage(),$stringOrException->getCode(),$stringOrException);
        return new MyApiError($stringOrException);
    }
}

class MyClient extends Client
{
    /**
     * @param $stringOrException
     * @return MyApiError
     */
    protected function getExceptionObject($stringOrException):\Throwable
    {
        if( $stringOrException instanceof \Throwable ) return new MyApiError($stringOrException->getMessage(),$stringOrException->getCode(),$stringOrException);
        return  new MyApiError($stringOrException);
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface|MyRequestResult
     * @throws MyApiError|\Throwable
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        if( $request instanceof MyApiRequest )      return $this->sendRealRequest($request);
        if( $request instanceof  Request )          return $this->sendRealRequest($request);

        throw $this->getExceptionObject('unknown request class: ' . get_class($request));
    }
}

$c = new MyClient('api.pmt', 'API_KEY');
$c->forceScheme = 'http';

$r = new MyApiRequest();
$r->event = 'SOME_EVENT';
$r->data['name'] = 'my name';

try {
    $result = $c->sendRequest($r);

    echo($result->returnData['field1']);
    //$r = MyRequestResult::fromServerResponse($c->sendRequest($r));
} catch (ApiException $e) {
    // error
} catch (\Throwable $e) {

}

echo($result->returnData['field1']);

// example 2

$r2 = new class() extends \PettyRest\Request {
    public $event = 'SAMPLE_EVENT';
    public $data = [
        'field1' => 'value',
        'field2' => 'value',
    ];

    public function getResponseDummy(): Response
    {
        return new class() extends Response{
            protected function getExceptionObject($stringOrException): \Throwable
            {
                if( $stringOrException instanceof \Throwable ) throw new ApiException($stringOrException->getMessage(),$stringOrException->getCode(),$stringOrException);
                throw new ApiException($stringOrException);
            }
        };
    }
};

//
