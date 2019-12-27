<?php

namespace Rapier;

use Rapier\View\Engines\FileEngine;
use Rapier\View\Engines\PhpEngine;
use Rapier\Filesystem\Filesystem;
use Rapier\View\Compilers\BladeCompiler;
use Rapier\View\Engines\CompilerEngine;
use Rapier\View\Engines\EngineResolver;
use Rapier\View\Factory;
use Rapier\View\FileViewFinder;
use Tightenco\Collect\Support\Arr;

class Blade
{
    /**
     * @var \Rapier\Filesystem\Filesystem
     */
    private $files;

    /**
     * @var \Rapier\View\Engines\EngineResolver
     */
    private $engines;

    /**
     * @var \Rapier\View\Factory
     */
    private $factory;

    /**
     * @var string
     */
    private $cachePath;

    /**
     * @var \Rapier\View\FileViewFinder
     */
    private $finder;

    /**
     * @var \Rapier\View\Compilers\BladeCompiler
     */
    private $bladeCompiler;

    /**
     * Blade constructor.
     *
     * @param string|array $viewPath
     * @param string       $cachePath
     */
    public function __construct($viewPath, string $cachePath)
    {
        $this->cachePath = $cachePath;

        $this->files = new Filesystem();

        $this->engines = new EngineResolver();

        $this->bladeCompiler = new BladeCompiler(
            $this->files,
            $this->cachePath
        );

        $this->finder = new FileViewFinder(
            $this->files,
            Arr::wrap($viewPath)
        );
    }

    /**
     * @param string $path
     * @param array  $data
     * @param array  $mergeData
     * @return string
     * @throws \Throwable
     */
    public function file(string $path, array $data = [], array $mergeData = []): string
    {
        return $this->getFactory()->file($path, $data, $mergeData);
    }

    /**
     * @param string $path
     * @param array  $data
     * @param array  $mergeData
     * @return string
     * @throws \Throwable
     */
    public function make(string $path, array $data = [], array $mergeData = []): string
    {
        return $this->getFactory()->make($path, $data, $mergeData);
    }

    /**
     * @param string $path
     * @param array  $data
     * @param array  $mergeData
     * @throws \Throwable
     */
    public function render(string $path, array $data = [], array $mergeData = []): void
    {
        echo $this->make($path, $data, $mergeData);
    }

    /**
     * @param callable $handler
     */
    public function setAuthHandler(callable $handler): void
    {
        $this->getFactory()->setAuthHandler($handler);
    }

    /**
     * @return \Rapier\Filesystem\Filesystem
     */
    public function getFilesystem(): Filesystem
    {
        return $this->files;
    }

    /**
     * @param \Rapier\Filesystem\Filesystem $filesystem
     */
    public function setFilesystem(Filesystem $filesystem): void
    {
        $this->files = $filesystem;
    }

    /**
     * @return \Rapier\View\Engines\EngineResolver
     */
    public function getEngines(): EngineResolver
    {
        return $this->engines;
    }

    /**
     * @return \Rapier\View\Factory
     */
    public function getFactory(): Factory
    {
        if ($this->factory === null) {
            $this->registerEngines();
            $this->factory = new Factory($this->engines, $this->finder);
        }

        return $this->factory;
    }

    private function registerEngines()
    {
        $this->engines->register('blade', function () {
            return new CompilerEngine($this->bladeCompiler);
        });

        $this->engines->register('php', function () {
            return new PhpEngine;
        });

        $this->engines->register('file', function () {
            return new FileEngine;
        });
    }
}