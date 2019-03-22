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

namespace Triotech\AdminerBundle\Adminer;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait AdminerPathAwareTrait
{
    /** @var string */
    protected $adminerPath;

    /**
     * @param string[]|string $path
     *
     * @return string
     */
    protected function getAdminerPath($path = ''): string
    {
        if (\is_array($path)) {
            $path = \implode(DIRECTORY_SEPARATOR, $path);
        }

        return $this->adminerPath . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * @param string $adminerPath
     */
    protected function setAdminerPath(string $adminerPath): void
    {
        $this->adminerPath = \realpath($adminerPath);

        if ($this->adminerPath === false) {
            throw new NotFoundHttpException('Adminer not found');
        }
    }
}
