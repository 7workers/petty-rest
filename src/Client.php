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
use Psr\Http\Message\StreamInterface;
class Client implements ClientInterface
{
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

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @throws ApiException
     */
    public function sendRequest( RequestInterface $request ): ResponseInterface
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

            if(!empty($error)) $this->throwError($error);

            return new class($rawResponse) implements ResponseInterface {
                public $rawResponse;
                public function __construct($rawResponse){$this->rawResponse=$rawResponse;}
                public function getProtocolVersion(){return '1.1';}
                public function withProtocolVersion($version){return $this;}
                public function getHeaders(){return [];}
                public function hasHeader($name){return false;}
                public function getHeader($name){return [];}
                public function getHeaderLine($name){return '';}
                public function withHeader($name,$value){return $this;}
                public function withAddedHeader($name,$value){return $this;}
                public function withoutHeader($name){return $this;}
                public function getBody() {
                    return new class($this) implements StreamInterface {
                        private $response;
                        public function __construct($response){$this->response=$response;}
                        public function __toString(){return $this->getContents();}
                        public function close(){}
                        public function detach(){}
                        public function getSize(){return null;}
                        public function tell(){return 0;}
                        public function eof(){return false;}
                        public function isSeekable(){return false;}
                        public function seek($offset,$whence=SEEK_SET){}
                        public function rewind(){}
                        public function isWritable(){return false;}
                        public function write($string){}
                        public function isReadable(){return false;}
                        public function read($length){}
                        public function getContents(){return @$this->response->rawResponse;}
                        public function getMetadata($key=null){return null;}};}
                public function withBody(StreamInterface $body){return $this;}
                public function getStatusCode(){return 202;}
                public function withStatus($code,$reasonPhrase=''){return $this;}
                public function getReasonPhrase(){return '';}};
        }catch(\Throwable $e){$this->throwError($e);}
    }

    protected function throwError($e)
    {
        if( $e instanceof \Throwable ) throw new ApiException($e->getMessage(),$e->getCode(),$e);
        throw new ApiException($e);
    }
}