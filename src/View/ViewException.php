<?php

namespace Bladezero\View;

use ErrorException;
;
use Bladezero\Support\Reflector;

class ViewException extends ErrorException
{
    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        $exception = $this->getPrevious();

        if (Reflector::isCallable($reportCallable = [$exception, 'report'])) {
            return Container::getInstance()->call($reportCallable);
        }

        return false;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Bladezero\Http\Request  $request
     * @return \Bladezero\Http\Response
     */
    public function render($request)
    {
        $exception = $this->getPrevious();

        if ($exception && method_exists($exception, 'render')) {
            return $exception->render($request);
        }
    }
}
