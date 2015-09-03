<?php

namespace Sata\FakeServerApi\Test;

use League\Flysystem\FilesystemInterface;
use Psr\Http\Message\ServerRequestInterface;

trait MocksTrait
{
    /**
     * @param string $url
     * @param array $get
     * @param array $post
     *
     * @return ServerRequestInterface
     */
    protected function request($url = '', $get = [], $post = [])
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
}