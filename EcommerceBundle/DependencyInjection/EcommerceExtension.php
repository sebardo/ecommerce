<?php

namespace EcommerceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Braintree_Configuration;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EcommerceExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        if (isset($config['environment'])) {
            $container->setParameter('braintree.environment', $config['environment']);
            Braintree_Configuration::environment($config['environment']);
        }
        if (isset($config['merchant_id'])) {
            $container->setParameter('braintree.merchant_id', $config['merchant_id']);
            Braintree_Configuration::merchantId($config['merchant_id']);
        }
        if (isset($config['public_key'])) {
            $container->setParameter('braintree.public_key', $config['public_key']);
            Braintree_Configuration::publicKey($config['public_key']);
        }
        if (isset($config['private_key'])) {
            $container->setParameter('braintree.private_key', $config['private_key']);
            Braintree_Configuration::privateKey($config['private_key']);
        }
    }
    
  
}
