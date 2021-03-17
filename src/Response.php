<?php /** @noinspection PhpMissingReturnTypeInspection */ /** @noinspection PhpFullyQualifiedNameUsageInspection */ /** @noinspection PhpUnhandledExceptionInspection */ /** @noinspection PhpInconsistentReturnPointsInspection */ /** @noinspection ReturnTypeCanBeDeclaredInspection */ /** @noinspection PhpDocRedundantThrowsInspection */
namespace PettyRest;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

abstract class Response implements ResponseInterface
{
    abstract protected function getExceptionObject($stringOrException):\Throwable;

    public $rawResponse;

    public function hydrateFromRaw(string $rawResponse)
    {
        try {
            $this->rawResponse = $rawResponse;

            $d = @json_decode($rawResponse, true);

            if (!empty($d['error'])) {
                $error = $d['error'];
            } elseif (!is_array($d)) {
                $error = 'Error decoding server response. JSON Error:' . json_last_error_msg() . " BODY:\n" . $rawResponse;
            }

            if (isset($error)) throw $this->getExceptionObject($error);

            foreach($d as $k=>$v){$this->{$k}=$v;}

        } catch (\Throwable $e) {
            throw $this->getExceptionObject($e);
        }
    }

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
            /** @noinspection PhpToStringReturnInspection */
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
    public function getReasonPhrase(){return '';}
}