<?php /** @noinspection PhpDocRedundantThrowsInspection */

namespace PettyRest;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class Response
{
    /**
     * @param ResponseInterface $response
     *
     * @return static
     * @throws ApiException
     */
    public static function fromServerResponse(ResponseInterface $response): self
    {
        try {

            $d = @json_decode($response->getBody()->getContents(), true);

            if (!empty($d['error'])) {
                $error = $d['error'];
            } elseif (!is_array($d)) {
                $error = 'Error decoding server response '.json_last_error_msg();
            }

            if( isset($error) ) static::throwError($error);

            $rsp = new static();

            foreach($d as $k=>$v){$rsp->{$k}=$v;}

            return $rsp;

        } catch (Throwable $e ) { static::throwError($e); }
    }

    protected static function throwError($e)
    {
        if( $e instanceof Throwable )   throw new ApiException($e->getMessage(), $e->getCode(), $e);
        throw new ApiException($e);
    }
}