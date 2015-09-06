<?php

namespace Sata\FakeServerApi\Test\Unit\DataProvider;

use GuzzleHttp\Exception\BadResponseException;
use Sata\FakeServerApi\DataProvider\ProxyDataProvider;
use Sata\FakeServerApi\Test\MocksTrait;

class ProxyDataProviderTest extends \PHPUnit_Framework_TestCase
{
    use MocksTrait;
    
    protected $provider;

    public function testDataRequestRightUri()
    {
        $guzzle = $this->guzzle([
            '/api/some/url' => 'api/some/url content',
            '/api/another/url' => 'api/another/url content',
        ]);
        $cache = $this->cache();
        $provider = new ProxyDataProvider($guzzle, $cache);
        
        $request = $this->request('/api/some/url');
        $data = $provider->data($request);
        $this->assertEquals('api/some/url content', $data);

        $request = $this->request('/api/another/url');
        $data = $provider->data($request);
        $this->assertEquals('api/another/url content', $data);
    }

    public function testDataReturnsBodyEvenIfBadRequestException()
    {
        $guzzle = $this->guzzle();
        $guzzle->method('send')
            ->willThrowException(new BadResponseException(
                'test exception',
                $this->request(),
                $this->response('body with exception')
            ));
        $cache = $this->cache();
        $provider = new ProxyDataProvider($guzzle, $cache);

        $request = $this->request('/api/some/url');
        $data = $provider->data($request);
        $this->assertEquals('body with exception', $data);
    }
}