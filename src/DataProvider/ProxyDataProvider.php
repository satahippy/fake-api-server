<?php

namespace Sata\FakeServerApi\DataProvider;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ServerRequestInterface;

class ProxyDataProvider implements IDataProvider
{
    /**
     * @var ClientInterface
     */
    protected $guzzle;

    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * @param ServerRequestInterface $request
     * 
     * @return mixed
     */
    public function data(ServerRequestInterface $request)
    {
        $request = $request->withUri(
            $request->getUri()
                ->withHost('')
                ->withPort(80)
                ->withScheme('')
        );
        try {
            $response = $this->guzzle->send($request);
        } catch (BadResponseException $exception) {
            $response = $exception->getResponse();
        }

        return (string)$response->getBody();
    }
}