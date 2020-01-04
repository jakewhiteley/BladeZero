<?php

namespace Unseenco\Blade\View\Concerns;

trait ProvidesHandlers
{
    /**
     * @var callable
     */
    private $authHandler;

    /**
     * @var callable
     */
    private $injectHandler;

    /**
     * @var callable
     */
    private $canHandler;

    /**
     * Set handler to resolve @auth directives.
     *
     * @param callable $handler
     */
    public function setAuthHandler(callable $handler): void
    {
        $this->authHandler = $handler;
    }

    /**
     * Set handler to resolve @can directives.
     *
     * @param callable $handler
     */
    public function setCanHandler(callable $handler): void
    {
        $this->canHandler = $handler;
    }

    /**
     * Set the handler to resolve services via @inject directive.
     *
     * @param callable $handler
     */
    public function setInjectHandler(callable $handler): void
    {
        $this->injectHandler = $handler;
    }

    /**
     * @param string|null $guard
     * @return bool
     */
    public function authHandler(string $guard = null): bool
    {
        if ($this->authHandler === null) {
            $this->authHandler = [$this, 'defaultAuthHandler'];
        }

        return \call_user_func($this->authHandler, $guard);
    }

    /**
     * @param       $abilities
     * @param array $arguments
     * @return bool
     */
    public function canHandler($abilities, $arguments = []): bool
    {
        if ($this->canHandler === null) {
            $this->canHandler = [$this, 'defaultCanHandler'];
        }

        return \call_user_func($this->canHandler, $abilities, $arguments);
    }

    /**
     * @param       $abilities
     * @param array $arguments
     * @return bool
     */
    public function canHandlerAny($abilities, $arguments = []): bool
    {
        return collect($abilities)->contains(function ($ability) use ($arguments) {
            return $this->canHandler($ability, $arguments);
        });
    }

    /**
     * @param string $service
     * @return mixed
     */
    public function injectHandler(string $service)
    {
        if ($this->injectHandler === null) {
            $this->injectHandler = [$this, 'defaultInjectHandler'];
        }

        return \call_user_func($this->injectHandler, $service);
    }

    /**
     * Default auth handler.
     *
     * @param string|null $guard
     * @return bool
     */
    protected function defaultAuthHandler(string $guard = null): bool
    {
        return false;
    }

    /**
     * Default can handler.
     *
     * @param string|array $abilities
     * @param string|array $arguments
     * @return bool
     */
    protected function defaultCanHandler($abilities, $arguments = []): bool
    {
        return false;
    }

    /**
     * Default service injection handler.
     *
     * @param string $service
     * @return object
     */
    protected function defaultInjectHandler(string $service)
    {
        return "Injected {$service}";
    }
}