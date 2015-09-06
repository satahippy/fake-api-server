<?php

namespace Sata\FakeServerApi\DataProvider;

use Doctrine\Common\Cache\Cache;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ServerRequestInterface;

class ProxyDataProvider implements IDataProvider
{
    /**
     * @var ClientInterface
     */
    protected $guzzle;
    
    /**
     * @var Cache
     */
    protected $cache;

    public function __construct(ClientInterface $guzzle, Cache $cache)
    {
        $this->guzzle = $guzzle;
        $this->cache = $cache;
    }

    /**
     * @param ServerRequestInterface $request
     * 
     * @return mixed
     */
    public function data(ServerRequestInterface $request)
    {
        $hash = $this->requestHash($request);
        if ($this->cache->contains($hash)) {
            return $this->cache->fetch($hash);
        }

        $request = $request->withUri(
            $request->getUri()
                ->withHost('')
                ->withPort(80)
                ->withScheme('')
        )->withoutHeader('Host');

        try {
            $response = $this->guzzle->send($request);
        } catch (BadResponseException $exception) {
            $response = $exception->getResponse();
        }

        $responseBody = (string)$response->getBody();
        $this->cache->save($hash, $responseBody);
        return $responseBody;
    }

    /**
     * @param ServerRequestInterface $request
     * 
     * @return string
     */
    public function requestHash(ServerRequestInterface $request)
    {
        $uri = $request->getUri();
        $get = $request->getQueryParams();
        $post = $request->getParsedBody();

        $hash = [
            'path' => $uri->getPath(),
            'get' => $get,
            'post' => $post
        ];

        return serialize($hash);
    }
}