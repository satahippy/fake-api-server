<?php

namespace Sata\FakeServerApi\Test\Unit\DataProvider;

use Sata\FakeServerApi\DataProvider\FileDataProvider;
use Sata\FakeServerApi\Test\MocksTrait;

class FileDataProviderTest extends \PHPUnit_Framework_TestCase
{
    use MocksTrait;
    
    protected $provider;

    public function testDataReturnsFileContent()
    {
        $file = 'test-data.json';
        $filesystem = $this->filesystem([
            $file => '{"field": "value"}'
        ]);
        $request = $this->request();
        $provider = new FileDataProvider($filesystem, $file);
        
        $data = $provider->data($request);
        
        $this->assertEquals('{"field": "value"}', $data);
    }
}