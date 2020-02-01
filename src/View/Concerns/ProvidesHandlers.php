<?php

namespace Unseenco\Blade\View\Concerns;


use Unseenco\Blade\Support\Str;

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
     * @var callable
     */
    private $errorHandler;

    /**
     * @var callable
     */
    private $csrfHandler;

    /**
     * Set handler to generate csrf tokens.
     *
     * @param callable $handler
     */
    public function setCsrfHandler(callable $handler): void
    {
        $this->csrfHandler = $handler;
    }

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
     * Set the handler to run via the @error directive.
     *
     * @param callable $handler
     */
    public function setErrorHandler(callable $handler): void
    {
        $this->errorHandler = $handler;
    }

    /**
     * Return the current user's csrf token.
     *
     * @return string
     */
    public function getCsrfToken(): string
    {
        if ($this->csrfHandler === null) {
            $this->csrfHandler = [$this, 'defaultCsrfHandler'];
        }

        return \call_user_func($this->csrfHandler);
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
    public function canAnyHandler($abilities, $arguments = []): bool
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
     * @param string $error
     * @return mixed
     */
    public function errorHandler(string $error)
    {
        if ($this->errorHandler === null) {
            $this->errorHandler = [$this, 'defaultErrorHandler'];
        }

        return \call_user_func($this->errorHandler, $error);
    }

    /**
     * Default csrf token generation.
     *
     * A real implementation should save this value to the session or some other store to allow validation.
     *
     * @return string
     */
    public function defaultCsrfHandler(): string
    {
        return Str::random(40);
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

    /**
     * Default error handler.
     *
     * @param string $error
     * @return string|false
     */
    protected function defaultErrorHandler(string $error)
    {
        return false;
    }
}