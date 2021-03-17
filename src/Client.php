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
    /**
     * @param $stringOrException
     * @see Client::getExceptionObject__dummy()
     */
    abstract protected function getExceptionObject($stringOrException):\Throwable;
    abstract public function sendRequest(RequestInterface $request): ResponseInterface;

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

    public function sendRealRequest( Request $request ): Response
    {
        try {
            /** @var Request $request */
            $request = $request->withHeader('Authorization', 'Bearer '.$this->apiKey);
            $request->setApiServerHost($this->host);

            if (null!==$this->forceScheme) $request->setApiServerScheme($this->forceScheme);
            if (null!==$this->targetPrefix) $request->setTargetPrefix($this->targetPrefix);

            $arHeaders_send=[];

            foreach ($request->getHeaders() as $name=>$values){$arHeaders_send[]=$name.': '.implode(', ',$values);}

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,               (string)$request->getUri());
            curl_setopt($ch, CURLOPT_HEADER,            0);
            curl_setopt($ch, CURLOPT_VERBOSE,           0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,    true);
            curl_setopt($ch, CURLOPT_POST,              true);
            curl_setopt($ch, CURLOPT_TIMEOUT,           $this->timeout);
            curl_setopt($ch, CURLOPT_HTTPHEADER,        $arHeaders_send);
            curl_setopt($ch, CURLOPT_POSTFIELDS,        $request->getBody()->getContents() );

            $rawResponse = @curl_exec($ch);
            $error       = curl_error($ch);

            curl_close($ch);

            if(!empty($error)) throw $this->getExceptionObject($error);

            $responseObject = $request->getResponseDummy();
            $responseObject->hydrateFromRaw($rawResponse);

            return $responseObject;

        }catch(\Throwable $e){throw $this->getExceptionObject($e);}
    }

    private function getExceptionObject__dummy($stringOrException): \Throwable
    {
        if( $stringOrException instanceof \Throwable ) return new ApiException($stringOrException->getMessage(),$stringOrException->getCode(),$stringOrException);
        return new ApiException($stringOrException);
    }
}