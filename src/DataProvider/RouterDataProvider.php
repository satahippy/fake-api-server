<?php

namespace Sata\FakeServerApi\DataProvider;

use Psr\Http\Message\ServerRequestInterface;

class RouterDataProvider implements IDataProvider
{
    /**
     * @var array
     */
    protected $routes;

    /**
     * @var \FastRoute\Dispatcher
     */
    protected $dispatcher;

    /**
     * @param array $routes
     */
    public function __construct($routes)
    {
        $this->routes = $routes;
        $this->dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) use ($routes) {
            foreach ($routes as $route => $provider) {
                $r->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'], $route, $provider);
            }
        });
    }

    /**
     * @param ServerRequestInterface $request
     * 
     * @return mixed
     */
    public function data(ServerRequestInterface $request)
    {
        /** @var IDataProvider $provider */
        list($result, $provider, $parameters) = $this->dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath()) + [null, null, []];

        switch ($result) {
            case \FastRoute\Dispatcher::FOUND:
                $queryParamsFromRoute = array_merge($request->getQueryParams(), $parameters);
                $request = $request->withQueryParams($queryParamsFromRoute);
                return $provider->data($request);
            default:
                return null;
        }
    }
}