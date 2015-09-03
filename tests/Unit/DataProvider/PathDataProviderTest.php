<?php

namespace Sata\FakeServerApi\Test\Unit\DataProvider;

use Sata\FakeServerApi\DataProvider\PathDataProvider;
use Sata\FakeServerApi\Test\MocksTrait;

class PathDataProviderTest extends \PHPUnit_Framework_TestCase
{
    use MocksTrait;
    
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

    public function testCustomPostfix()
    {
        $filesystem = $this->filesystem([
            'api/some/url/default.txt' => 'plain text'
        ]);
        $provider = new PathDataProvider($filesystem, [], '.txt');

        $data = $provider->data($this->request('/api/some/url'));
        $this->assertEquals('plain text', $data);
    }

    public function testDefaultPostfix()
    {
        $filesystem = $this->filesystem([
            'api/some/url/default.json' => 'some json'
        ]);
        $provider = new PathDataProvider($filesystem);

        $data = $provider->data($this->request('/api/some/url'));
        $this->assertEquals('some json', $data);
    }
}