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

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\Loader,
    Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * ScribeStripeExtension
 */
class ScribeStripeExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration(
            $configuration, 
            $configs
        );

        $container->setParameter(
            'scribe_stripe.api_key',
            $config['api_key']
        );

        $container->setParameter(
            'scribe_stripe.verify_ssl_certificates',
            $config['verify_ssl_certificates']
        );

        $container->setParameter(
            'scribe_stripe.log_activity',
            $config['log_activity']
        );

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');
    }
}
