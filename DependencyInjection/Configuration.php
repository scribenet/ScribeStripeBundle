<?php
/*
 * This file is part of the Scribe World Application.
 *
 * (c) Scribe Inc. <scribe@scribenet.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scribe\StripeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('scribe_stripe');

        $rootNode
            ->children()
                ->scalarNode('api_key')
                    ->isRequired()
                    ->info('The key to connect to the Stripe API')
                ->end()
                ->booleanNode('verify_ssl_certificates')
                    ->defaultTrue()
                    ->info('Should SSL certificates be verified?')
                ->end()
                ->booleanNode('log_activity')
                    ->defaultFalse()
                    ->info('Log activity using Monolog')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
