<?php

namespace Rapier\View\Concerns;

trait ProvidesHandlers
{
    private $authHandler;

    public function setAuthHandler(callable $handler): void
    {
        $this->authHandler = $handler;
    }

    public function authHandler(string $guard = null): bool
    {
        if ($this->authHandler === null) {
            $this->authHandler = function(string $guard = null) {
                return false;
            };
        }

        return \call_user_func($this->authHandler, $guard);
    }
}