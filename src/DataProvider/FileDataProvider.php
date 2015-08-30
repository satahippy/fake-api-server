<?php

namespace Sata\FakeServerApi\DataProvider;

use League\Flysystem\FilesystemInterface;
use Psr\Http\Message\ServerRequestInterface;

class FileDataProvider
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $file;

    /**
     * @param FilesystemInterface $filesystem
     * @param string $file
     */
    public function __construct(FilesystemInterface $filesystem, $file)
    {
        $this->filesystem = $filesystem;
        $this->file = $file;
    }

    /**
     * @param ServerRequestInterface $request
     * 
     * @return mixed
     */
    public function data(ServerRequestInterface $request)
    {
        return $this->filesystem->read($this->file);
    }
}