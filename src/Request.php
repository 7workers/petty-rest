<?php /** @noinspection DuplicatedCode */ /** @noinspection PhpToStringReturnInspection */ /** @noinspection PhpMissingReturnTypeInspection */ /** @noinspection PhpDocRedundantThrowsInspection */ /** @noinspection ReturnTypeCanBeDeclaredInspection */
namespace PettyRest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
abstract class Request implements RequestInterface
{
    protected $method = 'POST';
    protected $scheme = 'https';
    protected $target;
    protected $host;
    protected $arHeaders;
    protected $bodyJson;

    abstract public function getResponseDummy(): Response;

    public function __construct( string $target, $objData=null )
    {
        $this->target = $target;

        $this->arHeaders = ['Content-Type'=>['application/json']];

        if(is_object($objData)) $this->bodyJson=json_encode(array_filter(array_map(static function($in){if(!is_array($in))return $in;if(empty($in))return null;$in=array_filter($in,static function($x){if(null===$x)return false;if(is_array($x)&&empty($x))return false;return true;});if(empty($in))return null;return $in;},get_object_vars($objData)),static function($x){return null!==$x;}),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }

    public function setApiServerHost(string $host): void { $this->host = $host; }
    public function setApiServerScheme(string $s): void { $this->scheme = $s; }
    public function setTargetPrefix(string $prefix): void { $this->target = $prefix.$this->target; }
    public function getHeaders(){return $this->arHeaders;}
    public function withUri(UriInterface $uri,$preserveHost=false){return $this;}
    public function hasHeader($name){return false;}
    public function getHeader($name){return $this->arHeaders[$name]??null;}
    public function getHeaderLine($name){return '';}
    public function withHeader($name,$value){ $new=clone $this;$new->arHeaders[$name]=[$value];return $new;}
    public function withAddedHeader($name,$value){return $this;}
    public function withoutHeader($name){return $this;}
    public function withBody(StreamInterface $body){return $this;}
    public function getRequestTarget(){return $this->target;}
    public function withRequestTarget($requestTarget){return $this;}
    public function getMethod(){return $this->method;}
    public function withMethod($method){$this->method=$method;return $this;}
    public function getProtocolVersion(){return '1.1';}
    public function withProtocolVersion($version){return $this;}
    public function getBody()
    {
        if( null === $this->bodyJson ) $this->bodyJson=json_encode(array_filter(array_map(static function($in){if(!is_array($in))return $in;if(empty($in))return null;$in=array_filter($in,static function($x){if(null===$x)return false;if(is_array($x)&&empty($x))return false;return true;});if(empty($in))return null;return $in;},get_object_vars($this)),static function($x){return null!==$x;}),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        return new class($this->bodyJson) implements StreamInterface{
            private $json;
            public function __construct($json){$this->json=$json;}
            public function __toString(){return $this->json;}
            public function getContents(){return $this->json;}
            public function getSize(){return mb_strlen($this->json);}
            public function close(){}
            public function detach(){}
            public function tell(){return 0;}
            public function eof(){return false;}
            public function isSeekable(){return false;}
            public function seek($offset,$whence=SEEK_SET){}
            public function rewind(){}
            public function isWritable(){return false;}
            public function write($string){return 0;}
            public function isReadable(){return false;}
            public function read($length){}
            public function getMetadata($key=null){return null;}};
    }
    public function getUri()
    {
        return new class($this->host,$this->target,$this->scheme) implements UriInterface{
            private $h;private $p;private $s;
            public function __construct($h,$p,$s){$this->h=$h;$this->p=$p;$this->s=$s;}
            public function getScheme(){return $this->s;}
            public function getHost(){return $this->h;}
            public function getPath(){return $this->p;}
            public function __toString(){return $this->getScheme().'://'.$this->h.$this->p;}
            public function getAuthority(){return'';}
            public function getUserInfo(){return'';}
            public function getPort(){return null;}
            public function getQuery(){return'';}
            public function getFragment(){return'';}
            public function withScheme($scheme){return $this;}
            public function withUserInfo($user,$password=null){return $this;}
            public function withHost($host){return $this;}
            public function withPort($port){return $this;}
            public function withPath($path){return $this;}
            public function withQuery($query){return $this;}
            public function withFragment($fragment){return $this;}};
    }


}