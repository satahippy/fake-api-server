<?php

namespace Sata\FakeServerApi\Test\Integration\DataProvider;

use Doctrine\Common\Cache\ArrayCache;
use Sata\FakeServerApi\DataProvider\ProxyDataProvider;
use Sata\FakeServerApi\Test\MocksTrait;

class ProxyDataProviderTest extends \PHPUnit_Framework_TestCase
{
    use MocksTrait;

    public function testDataDoesNotRequestProxyIfCacheIsExists()
    {
        $guzzle = $this->guzzle([
            '/api/some/url' => 'api/some/url content'
        ]);
        $guzzle->expects($this->once())
            ->method('send');
        $cache = new ArrayCache();

        $provider = new ProxyDataProvider($guzzle, $cache);

        // first request
        $request = $this->request('/api/some/url');
        $provider->data($request);
        // second request
        $request = $this->request('/api/some/url');
        $provider->data($request);
    }

    public function testCacheContainsProperData()
    {
        $guzzle = $this->guzzle([
            '/api/some/url' => 'api/some/url content'
        ]);
        $cache = new ArrayCache();

        $provider = new ProxyDataProvider($guzzle, $cache);

        // first request
        $request = $this->request('/api/some/url');
        $original = $provider->data($request);
        // second request
        $request = $this->request('/api/some/url');
        $cached = $provider->data($request);
        
        $this->assertEquals($original, $cached);
    }

    public function testDifferentCacheForDifferentRequests()
    {
        $guzzle = $this->guzzle([
            '/api/some/url' => 'api/some/url content',
            '/api/another/url' => 'api/another/url content',
        ]);
        $cache = new ArrayCache();

        $provider = new ProxyDataProvider($guzzle, $cache);

        // first request
        $request = $this->request('/api/some/url');
        $provider->data($request);
        // second request
        $request = $this->request('/api/another/url');
        $provider->data($request);
        
        // get data from cache
        // first
        $request = $this->request('/api/some/url');
        $first = $provider->data($request);
        // second request
        $request = $this->request('/api/another/url');
        $second = $provider->data($request);

        $this->assertEquals('api/some/url content', $first);
        $this->assertEquals('api/another/url content', $second);
    }
}