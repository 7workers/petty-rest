<?php namespace MyNameSpace;

use PettyRest\Response;

$o = new class() {
    public $event = 'SAMPLE_EVENT';
    public $data = [
        'field1' => 'value',
        'field2' => 'value',
    ];
};

$c = new \PettyRest\Client('api.somehost.com', 'API_KEY');
$r = new \PettyRest\Request('/validate/', $o);

$r = Response::fromServerResponse($c->sendRequest($r));
