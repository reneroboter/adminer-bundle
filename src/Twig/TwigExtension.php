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

namespace Triotech\AdminerBundle\Twig;

use Triotech\AdminerBundle\Adminer\AdminerPathAwareTrait;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    use AdminerPathAwareTrait;

    public function __construct($adminerPath)
    {
        $this->setAdminerPath($adminerPath);
    }

    /** @inheritdoc */
    public function getFunctions()
    {
        return [
            new TwigFunction('adminer_favicon', [$this, 'dumpAdminerFavicon']),
        ];
    }

    /**
     * @return string
     */
    public function dumpAdminerFavicon()
    {
        return \sprintf('data:image/png;base64,%s', \base64_encode(\file_get_contents($this->getAdminerPath(['public', 'images', 'favicon.png']))));
    }
}
