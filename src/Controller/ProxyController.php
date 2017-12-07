<?php
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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Triotech\AdminerBundle\Adminer\AdminerPathAwareTrait;

class ProxyController extends Controller
{
    use AdminerPathAwareTrait;

    /** @inheritdoc */
    public function setContainer(ContainerInterface $container = null): void
    {
        parent::setContainer($container);

        if ($container instanceof ContainerInterface) {
            $this->setAdminerPath($container->getParameter('triotech.adminer_path'));
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function proxyAction(Request $request): Response
    {
        $this->disableProfiler();
        $response = $this->get('triotech.adminer.database_extractor')->updateAdminerDatabases($request);

        return $response instanceof Response ? $response : new Response(require 'file://' . $this->getAdminerPath(['public', 'index.php']));
    }

    /**
     * @param $type
     * @param $asset
     *
     * @return Response
     */
    public function assetAction($type, $asset): Response
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
     * @return self
     */
    protected function disableProfiler(): self
    {
        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }

        return $this;
    }
}
