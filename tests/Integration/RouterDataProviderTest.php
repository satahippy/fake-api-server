<?php

namespace Sata\FakeServerApi\Test\Integration\DataProvider;

use Psr\Http\Message\ServerRequestInterface;
use Sata\FakeServerApi\DataProvider\RouterDataProvider;
use Sata\FakeServerApi\Test\MocksTrait;

class RouterDataProviderTest extends \PHPUnit_Framework_TestCase
{
    use MocksTrait;

    public function testUsedDataProviderAccordinglyToRoute()
    {
        $provider = new RouterDataProvider([
            '/api/some/url' => $this->dataProvider('api/some/url data'),
            '/api/another/url' => $this->dataProvider('api/another/url data'),
        ]);

        $request = $this->request('/api/some/url');
        $data = $provider->data($request);
        $this->assertEquals('api/some/url data', $data);

        $request = $this->request('/api/another/url');
        $data = $provider->data($request);
        $this->assertEquals('api/another/url data', $data);
    }

    public function testDataReturnsNullIfRouteIsNotFound()
    {
        $provider = new RouterDataProvider([]);

        $request = $this->request('/api/some/url');
        $data = $provider->data($request);
        $this->assertEquals(null, $data);
    }

    public function testSetRequestMatchedParameters()
    {
        $assertRequestContainsParametersFromRoute = function (ServerRequestInterface $request) {
            $parameters = $request->getQueryParams();
            return $parameters['param1'] === 'val1'
            && $parameters['param2'] === 'val2'
            && $parameters['param3'] === 'val3';
        };
        $routeProvider = $this->getMock('\Sata\FakeServerApi\DataProvider\IDataProvider');
        $routeProvider->method('data')
            ->with($this->callback($assertRequestContainsParametersFromRoute));
        
        $provider = new RouterDataProvider([
            '/api/some/url/{param1}/{param2}' => $routeProvider,
        ]);

        $request = $this->request('/api/some/url/val1/val2', ['param3' => 'val3']);
        $provider->data($request);
    }

    public function testHttpMethodDoesNotMatter()
    {
        $provider = new RouterDataProvider([
            '/api/some/url' => $this->dataProvider('api/some/url data'),
        ]);
        
        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'];
        foreach ($methods as $method) {
            $request = $this->request('/api/some/url', [], [], $method);
            $data = $provider->data($request);
            $this->assertEquals('api/some/url data', $data);
        }
    }
}