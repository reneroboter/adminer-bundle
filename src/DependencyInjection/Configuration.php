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

namespace Triotech\AdminerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /** @inheritdoc */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('triotech_adminer');
        // @formatter:off
        $rootNode
            ->children()
                ->scalarNode('adminer_path')
                    ->cannotBeEmpty()
                    ->defaultValue('%kernel.root_dir%/../vendor/triotech/adminer/output')
                ->end()
            ->end()
        ;
        // @formatter:on

        return $treeBuilder;
    }
}
