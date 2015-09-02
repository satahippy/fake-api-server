<?php

namespace Sata\FakeServerApi\Test\Unit\DataProvider;

use League\Flysystem\FilesystemInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sata\FakeServerApi\DataProvider\PathDataProvider;

class PathDataProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testDataReturnsFileContent()
    {
        $filesystem = $this->filesystem([
            'api/some/url/default.json' => 'api/some/url/default.json content',
            'api/another/url/default.json' => 'api/another/url/default.json content',
        ]);
        $provider = new PathDataProvider($filesystem);

        $data = $provider->data($this->request('/api/some/url'));
        $this->assertEquals('api/some/url/default.json content', $data);

        $data = $provider->data($this->request('/api/another/url'));
        $this->assertEquals('api/another/url/default.json content', $data);
    }

    public function testDataReturnsNullIfFileIsNotFound()
    {
        $filesystem = $this->filesystem();
        $provider = new PathDataProvider($filesystem);

        $data = $provider->data($this->request('/api/some/url'));
        $this->assertEquals(null, $data);
    }

    public function testConsiderRequestParameters()
    {
        $filesystem = $this->filesystem([
            'api/some/url/default.json' => 'api/some/url/default.json content',
            'api/some/url/param1_1.json' => 'api/some/url/param1_1.json content',
            'api/some/url/param1_2.json' => 'api/some/url/param1_2.json content',
            'api/some/url/param1_1_param2_testparam2.json' => 'api/some/url/param1_1_param2_testparam2.json content'
        ]);
        $provider = new PathDataProvider($filesystem, ['param1', 'param2']);

        $data = $provider->data($this->request('/api/some/url'));
        $this->assertEquals('api/some/url/default.json content', $data);

        $data = $provider->data($this->request('/api/some/url', ['param1' => 1]));
        $this->assertEquals('api/some/url/param1_1.json content', $data);

        $data = $provider->data($this->request('/api/some/url', ['param1' => 2]));
        $this->assertEquals('api/some/url/param1_2.json content', $data);

        $data = $provider->data($this->request('/api/some/url', ['param1' => 1], ['param2' => 'testparam2']));
        $this->assertEquals('api/some/url/param1_1_param2_testparam2.json content', $data);
    }

    public function testIfFileWithParametersIsNotFoundThenReturnsDefaultFile()
    {
        $filesystem = $this->filesystem([
            'api/some/url/default.json' => 'api/some/url/default.json content'
        ]);
        $provider = new PathDataProvider($filesystem, ['param1', 'param2']);

        $data = $provider->data($this->request('/api/some/url', ['param1' => 1]));
        $this->assertEquals('api/some/url/default.json content', $data);
    }

    /**
     * @param string $url
     * @param array $get
     * @param array $post
     * 
     * @return ServerRequestInterface
     */
    protected function request($url, $get = [], $post = [])
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