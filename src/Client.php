<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpInconsistentReturnPointsInspection */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpMissingReturnTypeInspection */
/** @noinspection PhpToStringReturnInspection */
/** @noinspection ReturnTypeCanBeDeclaredInspection */
namespace PettyRest;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Client implements ClientInterface
{
    abstract protected function getExceptionClass(\Throwable $e):string;

    public $timeout = 1;
    public $forceScheme;

    protected $host;
    protected $apiKey;
    protected $targetPrefix;

    public function __construct(string $host, string $apiKey)
    {
        $this->host   = $host;
        $this->apiKey = $apiKey;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        /** @noinspection PhpParamsInspection */
        return $this->sendRealRequest($request);
    }

    public function sendRealRequest( Request $request ): Response
    {
        try {
            $request->setApiServerHost($this->host);

            if (null!==$this->forceScheme)  $request->setApiServerScheme($this->forceScheme);
            if (null!==$this->targetPrefix) $request->setTargetPrefix($this->targetPrefix);

            $arHeaders_send = ['Authorization: Bearer '.$this->apiKey];

            foreach($request->getHeaders() as $name=>$values){$arHeaders_send[]=$name.': '.implode(', ',$values);}

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,               (string)$request->getUri());
            curl_setopt($ch, CURLOPT_HEADER,            0);
            curl_setopt($ch, CURLOPT_VERBOSE,           0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,    true);
            curl_setopt($ch, CURLOPT_POST,              true);
            curl_setopt($ch, CURLOPT_TIMEOUT,           $this->timeout);
            curl_setopt($ch, CURLOPT_HTTPHEADER,        $arHeaders_send);
            curl_setopt($ch, CURLOPT_POSTFIELDS,        $request->getBody()->getContents());

            $rawResponse = @curl_exec($ch);
            $error       = curl_error($ch);

            curl_close($ch);

            if(!empty($error))$this->throwException(new ApiException($error));

            $responseObject = $request->getResponseDummy();
            $responseObject->hydrateFromRaw($rawResponse);

            return $responseObject;

        }catch(\Throwable $e){$this->throwException($e);}
    }

    protected function throwException(\Throwable $e)
    {
        $class = $this->getExceptionClass($e);
        throw new $class($e->getMessage(), $e->getCode(),$e);
    }
}