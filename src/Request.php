<?php /** @noinspection DuplicatedCode */ /** @noinspection PhpToStringReturnInspection */ /** @noinspection PhpMissingReturnTypeInspection */ /** @noinspection PhpDocRedundantThrowsInspection */ /** @noinspection ReturnTypeCanBeDeclaredInspection */
namespace PettyRest;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
abstract class Request implements RequestInterface
{
    protected string $method = 'POST';
    protected string $scheme = 'https';
    protected string $target;
    protected string $host;
    protected ?string $bodyJson = null;
    protected array $arHeaders;

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
    public function getHeaders(): array {return $this->arHeaders;}
    public function withUri(UriInterface $uri, bool $preserveHost=false): RequestInterface {return $this;}
    public function hasHeader(string $name): bool {return false;}
    public function getHeader(string $name): array {return $this->arHeaders[$name]??[];}
    public function getHeaderLine(string $name): string {return '';}
    public function withHeader(string $name,$value): MessageInterface { $new=clone $this;$new->arHeaders[$name]=[$value];return $new;}
    public function withAddedHeader(string $name,$value): MessageInterface {return $this;}
    public function withoutHeader(string $name): MessageInterface {return $this;}
    public function withBody(StreamInterface $body): MessageInterface {return $this;}
    public function getRequestTarget(): string {return $this->target;}
    public function withRequestTarget(string $requestTarget): RequestInterface {return $this;}
    public function getMethod(): string {return $this->method;}
    public function withMethod(string $method): RequestInterface {$this->method=$method;return $this;}
    public function getProtocolVersion(): string {return '1.1';}
    public function withProtocolVersion(string $version): MessageInterface {return $this;}
    public function getBody(): StreamInterface
    {
        if(null===$this->bodyJson)$this->bodyJson=json_encode(array_filter(array_map(static function($in){if(!is_array($in))return $in;if(empty($in))return null;$in=array_filter($in,static function($x){if(null===$x)return false;if(is_array($x)&&empty($x))return false;return true;});if(empty($in))return null;return $in;},array_filter(get_object_vars($this),static function($k){return !in_array($k,['method','scheme','arHeaders','host','target','bodyJson']);},ARRAY_FILTER_USE_KEY)),static function($x){return null!==$x;}),JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        return new class($this->bodyJson) implements StreamInterface{
            private $json;
            public function __construct($json){$this->json=$json;}
            public function __toString(): string {return $this->json;}
            public function getContents(): string {return $this->json;}
            public function getSize(): ?int {return mb_strlen($this->json);}
            public function close(): void {}
            public function detach(){}
            public function tell(): int {return 0;}
            public function eof(): bool {return false;}
            public function isSeekable(): bool {return false;}
            public function seek(int $offset, int $whence=SEEK_SET): void {}
            public function rewind(): void {}
            public function isWritable(): bool {return false;}
            public function write(string $string): int {return 0;}
            public function isReadable(): bool {return false;}
            public function read(int $length): string {return '';}
            public function getMetadata(?string $key=null){return null;}};
    }
    public function getUri(): UriInterface
    {
        return new class($this->host,$this->target,$this->scheme) implements UriInterface{
            private $h;private $p;private $s;
            public function __construct($h,$p,$s){$this->h=$h;$this->p=$p;$this->s=$s;}
            public function getScheme(): string {return $this->s;}
            public function getHost(): string {return $this->h;}
            public function getPath(): string {return $this->p;}
            public function __toString(): string {return $this->getScheme().'://'.$this->h.$this->p;}
            public function getAuthority(): string {return'';}
            public function getUserInfo(): string {return'';}
            public function getPort(): ?int {return null;}
            public function getQuery(): string {return'';}
            public function getFragment(): string {return'';}
            public function withScheme(string $scheme): UriInterface {return $this;}
            public function withUserInfo(string $user, ?string $password=null): UriInterface {return $this;}
            public function withHost(string $host): UriInterface {return $this;}
            public function withPort(?int $port): UriInterface {return $this;}
            public function withPath(string $path): UriInterface {return $this;}
            public function withQuery(string $query): UriInterface {return $this;}
            public function withFragment(string $fragment): UriInterface {return $this;}};
    }
}