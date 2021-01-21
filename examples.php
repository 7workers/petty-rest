<?php
/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */

/** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace MyNameSpace;

use PettyRest\ApiException;

require __DIR__.'/../shared/vendor/autoload.php';
require 'src/Client.php';
require 'src/ApiException.php';
require 'src/Request.php';
require 'src/Response.php';

$c = new \PettyRest\Client('api.pmt', 'API_KEY');
$c->forceScheme = 'http';

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

}

class MyRequestResult extends \PettyRest\Response
{
    public $returnData = [
        'field1' => null,
        'field2' => null,
    ];

    /**
     * @param $e
     * @throws MyApiError
     */
    protected static function throwError($e):void
    {
        if( $e instanceof \Throwable )   throw new MyApiError($e->getMessage(), $e->getCode(), $e);
        throw new MyApiError($e);
    }
}

$r = new MyApiRequest();
$r->event = 'SOME_EVENT';
$r->data['name'] = 'my name';

try {
    $r = MyRequestResult::fromServerResponse($c->sendRequest($r));
} catch (ApiException $e) {
    // error
}

echo($r->returnData['field1']);

// example 2

$r2 = new class() extends \PettyRest\Request {
    public $event = 'SAMPLE_EVENT';
    public $data = [
        'field1' => 'value',
        'field2' => 'value',
    ];
};

$c->sendRequest($r2);