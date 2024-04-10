<?php

namespace Bladezero\Contracts\Support;

interface DeferringDisplayableValue
{
    /**
     * Resolve the displayable value that the class is deferring.
     *
     * @return \Bladezero\Contracts\Support\Htmlable|string
     */
    public function resolveDisplayableValue();
}
