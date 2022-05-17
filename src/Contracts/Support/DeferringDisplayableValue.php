<?php

namespace Bladezero\Contracts\Support;

interface DeferringDisplayableValue
{
    /**
     * Resolve the displayable value that the class is deferring.
     *
     * @return \Tightenco\Collect\Contracts\Support\Htmlable|string
     */
    public function resolveDisplayableValue();
}
