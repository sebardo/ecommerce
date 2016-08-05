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
        
        //braintree
        if (isset($config['providers']['braintree']['options']['environment'])) {
            $container->setParameter('braintree.environment', $config['providers']['braintree']['options']['environment']);
            Braintree_Configuration::environment($config['providers']['braintree']['options']['environment']);
        }
        if (isset($config['providers']['braintree']['options']['merchant_id'])) {
            $container->setParameter('braintree.merchant_id', $config['providers']['braintree']['options']['merchant_id']);
            Braintree_Configuration::merchantId($config['providers']['braintree']['options']['merchant_id']);
        }
        if (isset($config['providers']['braintree']['options']['public_key'])) {
            $container->setParameter('braintree.public_key', $config['providers']['braintree']['options']['public_key']);
            Braintree_Configuration::publicKey($config['providers']['braintree']['options']['public_key']);
        }
        if (isset($config['providers']['braintree']['options']['private_key'])) {
            $container->setParameter('braintree.private_key', $config['providers']['braintree']['options']['private_key']);
            Braintree_Configuration::privateKey($config['providers']['braintree']['options']['private_key']);
        }
        
        //paypal
        
        
    }
    
  
}
