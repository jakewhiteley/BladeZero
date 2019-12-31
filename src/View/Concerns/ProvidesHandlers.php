<?php

namespace Rapier\View\Concerns;

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
     * Set handler to resolve @auth directives.
     *
     * @param callable $handler
     */
    public function setAuthHandler(callable $handler): void
    {
        $this->authHandler = $handler;
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
     * Default Auth handler.
     *
     * @param string|null $guard
     * @return bool
     */
    protected function defaultAuthHandler(string $guard = null): bool
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