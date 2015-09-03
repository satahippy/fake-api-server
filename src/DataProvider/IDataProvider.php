<?php

namespace Sata\FakeServerApi\DataProvider;

use Psr\Http\Message\ServerRequestInterface;

interface IDataProvider
{
    /**
     * @param ServerRequestInterface $request
     *
     * @return mixed
     */
    public function data(ServerRequestInterface $request);
}