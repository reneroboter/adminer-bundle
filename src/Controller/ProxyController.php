<?php

declare(strict_types=1);

/**
 * This file is part of the TRIOTECH adminer-bundle project.
 *
 * @copyright TRIOTECH <open-source@triotech.fr>
 * @license https://joinup.ec.europa.eu/page/eupl-text-11-12 EUPL v1.2 or higher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Triotech\AdminerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Triotech\AdminerBundle\Adminer\AdminerPathAwareTrait;
use Triotech\AdminerBundle\Adminer\DatabaseExtractor;

final class ProxyController extends AbstractController
{
    use AdminerPathAwareTrait;

    /** @var DatabaseExtractor */
    private $databaseExtractor;
    /** @var Profiler|null */
    private $profiler;

    /**
     * @param string $adminerPath
     * @param DatabaseExtractor $databaseExtractor
     * @param Profiler|null $profiler
     */
    public function __construct(string $adminerPath, DatabaseExtractor $databaseExtractor, ?Profiler $profiler)
    {
        $this->setAdminerPath($adminerPath);

        $this->databaseExtractor = $databaseExtractor;
        $this->profiler = $profiler;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function proxyAction(Request $request): Response
    {
        $this->disableProfiler();
        $response = $this->databaseExtractor->updateAdminerDatabases($request);

        return $response instanceof Response ? $response : new Response(require 'file://' . $this->getAdminerPath(['public', 'index.php']));
    }

    /**
     * @param string $type
     * @param string $asset
     *
     * @return Response
     */
    public function assetAction(string $type, string $asset): Response
    {
        $this->disableProfiler();
        $response = new BinaryFileResponse($this->getAdminerPath(['public', $type, $asset]));

        if ($type === 'css') {
            $response->headers->set('Content-Type', 'text/css');
        } elseif ($type === 'js') {
            $response->headers->set('Content-Type', 'text/javascript');
        }

        return $response;
    }

    /**
     * Disabled Symfony Profiler
     */
    private function disableProfiler(): void
    {
        if (null !== $this->profiler) {
            $this->profiler->disable();
        }
    }
}
