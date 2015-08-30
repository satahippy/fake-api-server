<?php

namespace Sata\FakeServerApi\Test\Unit\DataProvider;

use League\Flysystem\FilesystemInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sata\FakeServerApi\DataProvider\FileDataProvider;

class FileDataProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $provider;

    public function testDataReturnsFileContent()
    {
        $file = 'test-data.json';
        $filesystem = $this->filesystem();
        $filesystem->method('read')
            ->with($file)
            ->willReturn('{"field": "value"}');
        $request = $this->request();
        $provider = new FileDataProvider($filesystem, $file);
        
        $data = $provider->data($request);
        
        $this->assertEquals('{"field": "value"}', $data);
    }

    /**
     * @return ServerRequestInterface
     */
    protected function request()
    {
        return $this->getMock('\Psr\Http\Message\ServerRequestInterface');
    }

    /**
     * @return FilesystemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function filesystem()
    {
        return $this->getMock('\League\Flysystem\FilesystemInterface');
    }
}