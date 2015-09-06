<?php

namespace Sata\FakeServerApi\Test;

use GuzzleHttp\ClientInterface;
use League\Flysystem\FilesystemInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sata\FakeServerApi\DataProvider\IDataProvider;

trait MocksTrait
{
    /**
     * @param string $url
     * @param array $get
     * @param array $post
     *
     * @return ServerRequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function request($url = '', $get = [], $post = [], $method = 'GET')
    {
        $uri = $this->getMock('\Psr\Http\Message\UriInterface');
        $uri->method('getPath')
            ->willReturn($url);
        $uri->method($this->matchesRegularExpression('/with.*/'))
            ->willReturnSelf();

        $request = $this->getMock('\Psr\Http\Message\ServerRequestInterface');
        $request->method('getUri')
            ->willReturn($uri);
        $request->method('getQueryParams')
            ->willReturn($get);
        $request->method('getParsedBody')
            ->willReturn($post);
        $request->method('getMethod')
            ->willReturn($method);
        $request->method('withQueryParams')
            ->willReturnCallback(function ($get) use ($url, $post, $method) {
                return $this->request($url, $get, $post, $method);
            });
        $request->method($this->matchesRegularExpression('/with.*/'))
            ->willReturnSelf();

        return $request;
    }

    /**
     * @param string $body
     *
     * @return ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function response($body = '')
    {
        $response = $this->getMock('\Psr\Http\Message\ResponseInterface');
        $response->method('getBody')
            ->willReturn($body);

        return $response;
    }

    /**
     * @param array $files
     *
     * @return FilesystemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function filesystem($files = [])
    {
        $filesystem = $this->getMock('\League\Flysystem\FilesystemInterface');
        $filesystem->method('read')
            ->willReturnCallback(function ($path) use ($files) {
                return $files[$path];
            });
        $filesystem->method('has')
            ->willReturnCallback(function ($path) use ($files) {
                return isset($files[$path]);
            });

        return $filesystem;
    }

    /**
     * @param array $responses
     *
     * @return ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function guzzle($responses = [])
    {
        $guzzle = $this->getMock('\GuzzleHttp\ClientInterface');
        $guzzle->method('send')
            ->willReturnCallback(function (RequestInterface $request) use ($responses) {
                $responseBody = $responses[$request->getUri()->getPath()];
                return $this->response($responseBody);
            });

        return $guzzle;
    }

    /**
     * @param mixed $data
     *
     * @return IDataProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function dataProvider($data)
    {
        $provider = $this->getMock('\Sata\FakeServerApi\DataProvider\IDataProvider');
        $provider->method('data')
            ->willReturn($data);

        return $provider;
    }
}