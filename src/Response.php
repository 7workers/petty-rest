<?php /** @noinspection PhpMissingReturnTypeInspection */ /** @noinspection PhpFullyQualifiedNameUsageInspection */ /** @noinspection PhpUnhandledExceptionInspection */ /** @noinspection PhpInconsistentReturnPointsInspection */ /** @noinspection ReturnTypeCanBeDeclaredInspection */ /** @noinspection PhpDocRedundantThrowsInspection */
namespace PettyRest;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

abstract class Response implements ResponseInterface
{
    public string $rawResponse;

    public function hydrateFromRaw(string $rawResponse): void
    {
        $this->rawResponse = $rawResponse;

        try {
            $d = json_decode($rawResponse, true, 512, JSON_THROW_ON_ERROR);

            if (!empty($d['error'])) {
                throw new ApiException($d['error'], $d['code'] ?? 0);
            }
        }
        catch (\JsonException $e) {
            throw new ApiException('Error decoding server response. JSON Error:' .
                $e->getMessage() . " BODY:\n" . $rawResponse);
        }

        foreach($d as $k=>$v){$this->{$k}=$v;}
    }

    public function getProtocolVersion(): string {return '1.1';}
    public function withProtocolVersion(string $version): MessageInterface {return $this;}
    public function getHeaders(): array {return [];}
    public function hasHeader(string $name): bool {return false;}
    public function getHeader(string $name): array {return [];}
    public function getHeaderLine(string $name): string {return '';}
    public function withHeader(string $name,$value): MessageInterface {return $this;}
    public function withAddedHeader(string $name,$value): MessageInterface {return $this;}
    public function withoutHeader(string $name): MessageInterface {return $this;}
    public function getBody(): StreamInterface {
        return new class($this) implements StreamInterface {
            private $response;
            public function __construct($response){$this->response=$response;}
            public function __toString(): string {return $this->getContents();}
            public function close(): void {}
            public function detach(){}
            public function getSize(): ?int {return null;}
            public function tell(): int {return 0;}
            public function eof(): bool {return false;}
            public function isSeekable(): bool {return false;}
            public function seek(int $offset,int $whence=SEEK_SET): void {}
            public function rewind(): void {}
            public function isWritable(): bool {return false;}
            public function write(string $string): int {return 0;}
            public function isReadable():bool {return false;}
            public function read(int $length): string {return '';}
            public function getContents(): string {return @$this->response->rawResponse;}
            public function getMetadata(?string $key=null){return null;}};}
    public function withBody(StreamInterface $body): MessageInterface {return $this;}
    public function getStatusCode(): int {return 202;}
    public function withStatus(int $code, string $reasonPhrase=''): ResponseInterface {return $this;}
    public function getReasonPhrase(): string {return '';}
}