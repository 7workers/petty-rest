<?php /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */ /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace SomeonesNameSpace;

use MyNameSpace\MyClient;
use PettyRest\ApiException;

require 'src/Client.php';
require 'src/ApiException.php';
require 'src/Request.php';
require 'src/Response.php';
require 'create_your_lib_example.php';

$c = new \MyNameSpace\MyClient('api.server', 'API_KEY');
$c->forceScheme = 'http';

function request_response_1(MyClient  $c)
{
    $r = new \MyNameSpace\MyApiRequest_A();
    $r->event = 'SOME_EVENT';
    $r->data['name'] = 'my name';

    try {
        $result = $c->getResult_A($r);
        echo($result->fieldA);
    } catch (ApiException $e) {
        // error
    } catch (\Throwable $e) {

    }
}

// example 2

$r2 = new class() extends \PettyRest\Request {
    public $event = 'SAMPLE_EVENT';
    public $data = [
        'field1' => 'value',
        'field2' => 'value',
    ];

    public function getResponseDummy(): \PettyRest\Response
    {
        return new class() extends \PettyRest\Response{
            public $filedLambda1 = false;
        };
    }
};

//
