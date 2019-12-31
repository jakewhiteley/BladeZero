<?php

namespace Rapier;

use InvalidArgumentException;
use Rapier\Contracts\Support\Arrayable;
use Rapier\Support\Str;
use Rapier\Support\Traits\Macroable;
use Rapier\View\Engines\FileEngine;
use Rapier\View\Engines\PhpEngine;
use Rapier\Filesystem\Filesystem;
use Rapier\View\Compilers\BladeCompiler;
use Rapier\View\Engines\CompilerEngine;
use Rapier\View\Engines\EngineResolver;
use Rapier\View\FileViewFinder;
use Rapier\View\ViewFinderInterface;
use Rapier\View\ViewName;
use Tightenco\Collect\Support\Arr;

class Blade
{
    use Macroable,
        View\Concerns\ManagesComponents,
        View\Concerns\ManagesLayouts,
        View\Concerns\ManagesLoops,
        View\Concerns\ManagesStacks,
        View\Concerns\ManagesTranslations,
        View\Concerns\ProvidesHandlers;

    /**
     * @var \Rapier\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Rapier\View\Engines\EngineResolver
     */
    protected $engines;

    /**
     * @var \Rapier\View\FileViewFinder
     */
    protected $finder;

    /**
     * @var \Rapier\View\Compilers\BladeCompiler
     */
    protected $bladeCompiler;

    /**
     * Data that should be available to all templates.
     *
     * @var array
     */
    protected $shared = [];

    /**
     * The extension to engine bindings.
     *
     * @var array
     */
    protected $extensions = [
        'blade.php' => 'blade',
        'php' => 'php',
        'css' => 'file',
        'html' => 'file',
    ];

    /**
     * The number of active rendering operations.
     *
     * @var int
     */
    protected $renderCount = 0;

    /**
     * Blade constructor.
     *
     * @param string|array $viewPath
     * @param string       $cachePath
     */
    public function __construct($viewPath, string $cachePath)
    {
        $this->files = new Filesystem();

        $this->registerEngines($cachePath);

        $this->finder = new FileViewFinder(
            $this->files,
            Arr::wrap($viewPath)
        );

        $this->share('__env', $this);
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $path
     * @param array  $data
     * @param array  $mergeData
     * @return string
     * @throws \Throwable
     */
    public function file($path, array $data = [], array $mergeData = []): string
    {
        $data = array_merge($this->getShared(), $mergeData, $data);

        return $this->render($path, $data);
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     * @return string
     * @throws \Throwable
     */
    public function make($view, $data = [], $mergeData = [])
    {
        $path = $this->finder->find(
            $view = $this->normalizeName($view)
        );

        // Next, we will create the view instance and call the view creator for the view
        // which can set any data, etc. Then we will return the view instance back to
        // the caller for rendering or performing other view manipulations on this.
        $data = array_merge($this->getShared(), $mergeData, $data);

        return $this->render($path, $data);
    }

    protected function render(string $path, $data = []): string
    {
        try {
            $contents = $this->renderContents($path, $data);

            $response = isset($callback) ? $callback($this, $contents) : null;

            // Once we have the contents of the view, we will flush the sections if we are
            // done rendering all views so that there is nothing left hanging over when
            // another view gets rendered in the future by the application developer.
            $this->flushStateIfDoneRendering();

            return !is_null($response) ? $response : $contents;
        } catch (\Exception $e) {
            $this->flushState();

            throw $e;
        } catch (\Throwable $e) {
            $this->flushState();

            throw $e;
        }
    }

    public function renderContents(string $path, $data = [])
    {
        $this->incrementRender();

        $contents = $this->getEngineFromPath($path)->get($path, $data);

        // Once we've finished rendering the view, we'll decrement the render count
        // so that each sections get flushed out next time a view is created and
        // no old sections are staying around in the memory of an environment.
        $this->decrementRender();

        return $contents;
    }

    /**
     * Get the first view that actually exists from the given list.
     *
     * @param array $views
     * @param array $data
     * @param array $mergeData
     * @return string
     *
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function first(array $views, array $data = [], array $mergeData = [])
    {
        $view = Arr::first($views, function ($view) {
            return $this->exists($view);
        });

        if (!$view) {
            throw new InvalidArgumentException('None of the views in the given array exist.');
        }

        return $this->make($view, $data, $mergeData);
    }

    /**
     * Get the rendered content of the view based on a given condition.
     *
     * @param bool   $condition
     * @param string $view
     * @param array  $data
     * @param array  $mergeData
     * @return string
     * @throws \Throwable
     */
    public function renderWhen($condition, string $view, array $data = [], array $mergeData = [])
    {
        if (!$condition) {
            return '';
        }

        return $this->make($view, $data, $mergeData);
    }

    /**
     * Get the rendered contents of a partial from a loop.
     *
     * @param string $view
     * @param array  $data
     * @param string $iterator
     * @param string $empty
     * @return string
     * @throws \Throwable
     */
    public function renderEach($view, $data, $iterator, $empty = 'raw|')
    {
        $result = '';

        // If is actually data in the array, we will loop through the data and append
        // an instance of the partial view to the final result HTML passing in the
        // iterated value of this data array, allowing the views to access them.
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $result .= $this->make(
                    $view, ['key' => $key, $iterator => $value]
                );
            }
        }

        // If there is no data in the array, we will render the contents of the empty
        // view. Alternatively, the "empty view" could be a raw string that begins
        // with "raw|" for convenience and to let this know that it is a string.
        else {
            $result = Str::startsWith($empty, 'raw|')
                ? substr($empty, 4)
                : $this->make($empty);
        }

        return $result;
    }

    /**
     * Determine if a given view exists.
     *
     * @param string $view
     * @return bool
     */
    public function exists($view)
    {
        try {
            $this->finder->find($view);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * Get the appropriate view engine for the given path.
     *
     * @param string $path
     * @return \Rapier\Contracts\View\Engine
     *
     * @throws \InvalidArgumentException
     */
    public function getEngineFromPath($path)
    {
        if (!$extension = $this->getExtension($path)) {
            throw new InvalidArgumentException("Unrecognized extension in file: {$path}");
        }

        $engine = $this->extensions[$extension];

        return $this->engines->resolve($engine);
    }

    /**
     * Get the extension used by the view file.
     *
     * @param string $path
     * @return string
     */
    protected function getExtension($path)
    {
        $extensions = array_keys($this->extensions);

        return Arr::first($extensions, function ($value) use ($path) {
            return Str::endsWith($path, '.' . $value);
        });
    }

    /**
     * Add a piece of shared data to the environment.
     *
     * @param array|string $key
     * @param mixed|null   $value
     * @return mixed
     */
    public function share($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            $this->shared[$key] = $value;
        }

        return $value;
    }

    /**
     * Increment the rendering counter.
     *
     * @return void
     */
    public function incrementRender()
    {
        $this->renderCount++;
    }

    /**
     * Decrement the rendering counter.
     *
     * @return void
     */
    public function decrementRender()
    {
        $this->renderCount--;
    }

    /**
     * Check if there are no active render operations.
     *
     * @return bool
     */
    public function doneRendering()
    {
        return $this->renderCount == 0;
    }

    /**
     * Add a location to the array of view locations.
     *
     * @param string $location
     * @return void
     */
    public function addLocation($location)
    {
        $this->finder->addLocation($location);
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param string       $namespace
     * @param string|array $hints
     * @return $this
     */
    public function addNamespace($namespace, $hints)
    {
        $this->finder->addNamespace($namespace, $hints);

        return $this;
    }

    /**
     * Prepend a new namespace to the loader.
     *
     * @param string       $namespace
     * @param string|array $hints
     * @return $this
     */
    public function prependNamespace($namespace, $hints): Blade
    {
        $this->finder->prependNamespace($namespace, $hints);

        return $this;
    }

    /**
     * Replace the namespace hints for the given namespace.
     *
     * @param string       $namespace
     * @param string|array $hints
     * @return $this
     */
    public function replaceNamespace($namespace, $hints): Blade
    {
        $this->finder->replaceNamespace($namespace, $hints);

        return $this;
    }

    /**
     * Register an "if" statement directive.
     *
     * @param  string  $name
     * @param  callable  $callback
     * @return void
     */
    public function if($name, callable $callback): void
    {
        $this->bladeCompiler->if($name,  $callback);
    }

    /**
     * Register a valid view extension and its engine.
     *
     * @param string        $extension
     * @param string        $engine
     * @param \Closure|null $resolver
     * @return void
     */
    public function addExtension($extension, $engine, $resolver = null): void
    {
        $this->finder->addExtension($extension);

        if (isset($resolver)) {
            $this->engines->register($engine, $resolver);
        }

        unset($this->extensions[$extension]);

        $this->extensions = array_merge([$extension => $engine], $this->extensions);
    }

    /**
     * Flush all of the factory state like sections and stacks.
     *
     * @return void
     */
    public function flushState(): void
    {
        $this->renderCount = 0;

        $this->flushSections();
        $this->flushStacks();
    }

    /**
     * Flush all of the section contents if done rendering.
     *
     * @return void
     */
    public function flushStateIfDoneRendering(): void
    {
        if ($this->doneRendering()) {
            $this->flushState();
        }
    }

    /**
     * Get the extension to engine bindings.
     *
     * @return array
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Get the engine resolver instance.
     *
     * @return \Rapier\View\Engines\EngineResolver
     */
    public function getEngineResolver(): EngineResolver
    {
        return $this->engines;
    }

    /**
     * Get the engine resolver instance.
     *
     * @return \Rapier\View\Compilers\BladeCompiler
     */
    public function getCompiler(): BladeCompiler
    {
        return $this->bladeCompiler;
    }

    /**
     * Get the view finder instance.
     *
     * @return \Rapier\View\ViewFinderInterface
     */
    public function getFinder(): ViewFinderInterface
    {
        return $this->finder;
    }

    /**
     * Get the Filesystem instance.
     *
     * @return Filesystem
     */
    public function getFiles(): Filesystem
    {
        return $this->files;
    }

    /**
     * Set the view finder instance.
     *
     * @param \Rapier\View\ViewFinderInterface $finder
     * @return void
     */
    public function setFinder(ViewFinderInterface $finder)
    {
        $this->finder = $finder;
    }

    /**
     * Flush the cache of views located by the finder.
     *
     * @return void
     */
    public function flushFinderCache()
    {
        $this->getFinder()->flush();
    }


    /**
     * Get an item from the shared data.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function shared($key, $default = null)
    {
        return Arr::get($this->shared, $key, $default);
    }

    /**
     * Get all of the shared data for the environment.
     *
     * @return array
     */
    public function getShared()
    {
        return $this->shared;
    }

    /**
     * Normalize a view name.
     *
     * @param string $name
     * @return string
     */
    protected function normalizeName($name)
    {
        return ViewName::normalize($name);
    }

    /**
     * Parse the given data into a raw array.
     *
     * @param mixed $data
     * @return array
     */
    protected function parseData($data)
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }

    protected function registerEngines(string $cachePath)
    {
        $this->engines = new EngineResolver();

        $this->bladeCompiler = new BladeCompiler(
            $this->files,
            $cachePath
        );

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