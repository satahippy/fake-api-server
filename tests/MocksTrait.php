<?php

namespace Sata\FakeServerApi\Test;

use League\Flysystem\FilesystemInterface;
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

        return $request;
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