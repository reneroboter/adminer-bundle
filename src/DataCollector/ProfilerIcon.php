<?php

namespace Triotech\AdminerBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

class ProfilerIcon implements DataCollectorInterface
{
    /** @inheritdoc */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    /** @inheritdoc */
    public function getName()
    {
        return 'profiler_adminer_icon';
    }

    /** @inheritdoc */
    public function reset()
    {
    }
}
