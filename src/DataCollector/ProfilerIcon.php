<?php

declare(strict_types=1);

namespace Triotech\AdminerBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

final class ProfilerIcon implements DataCollectorInterface
{
    /** @inheritdoc */
    public function collect(Request $request, Response $response, ?\Exception $exception = null): void
    {
    }

    /** @inheritdoc */
    public function getName()
    {
        return 'profiler_adminer_icon';
    }

    /** @inheritdoc */
    public function reset(): void
    {
    }
}
