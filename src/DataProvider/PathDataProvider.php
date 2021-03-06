<?php

namespace Sata\FakeServerApi\DataProvider;

use League\Flysystem\FilesystemInterface;
use Psr\Http\Message\ServerRequestInterface;

class PathDataProvider implements IDataProvider
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var string[]
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $postfix;

    /**
     * @param FilesystemInterface $filesystem
     * @param string[] $parameters
     * @param string $postfix
     */
    public function __construct(FilesystemInterface $filesystem, $parameters = [], $postfix = '.json')
    {
        $this->filesystem = $filesystem;
        $this->parameters = $parameters;
        $this->postfix = $postfix;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return mixed|null
     */
    public function data(ServerRequestInterface $request)
    {
        $url = $request->getUri()->getPath();
        $parameters = array_merge($request->getQueryParams(), $request->getParsedBody());

        $file = $this->file($url, $parameters);
        if (!$this->filesystem->has($file)) {
            $file = $this->defaultFile($url);
        }

        if (!$this->filesystem->has($file)) {
            return null;
        }

        return $this->filesystem->read($file);
    }

    /**
     * @param string $url
     *
     * @return string
     */
    protected function defaultFile($url)
    {
        return $this->file($url);
    }

    /**
     * @param string $url
     * @param array $parameters
     *
     * @return string
     */
    protected function file($url, $parameters = [])
    {
        $directory = trim($url, '/');

        $file = '';
        foreach ($this->parameters as $parameter) {
            if (isset($parameters[$parameter])) {
                $file .= $parameter . '_' . $parameters[$parameter] . '_';
            }
        }
        if (empty($file)) {
            $file = 'default';
        }
        $file = rtrim($file, '_') . $this->postfix;

        return $directory . '/' . $file;
    }
}