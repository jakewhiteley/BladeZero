<?php

namespace Bladezero\Contracts\Support;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return \Bladezero\Contracts\Support\MessageBag
     */
    public function getMessageBag();
}
