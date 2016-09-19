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
            
        $env = $container->getParameter("kernel.environment");
               
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        //ecommerce paramters
        if (isset($config['fixture_data'])) {
            $container->setParameter('ecommerce.fixture_data', $config['fixture_data']);
        }
        if (isset($config['currency_symbol'])) {
            $container->setParameter('ecommerce.currency_symbol', $config['currency_symbol']);
        }
        if (isset($config['advert_unit_price'])) {
            $container->setParameter('ecommerce.advert_unit_price', $config['advert_unit_price']);
        }
        if (isset($config['vat'])) {
            $container->setParameter('ecommerce.vat', $config['vat']);
        }
        if (isset($config['special_percentage_charge'])) {
            $container->setParameter('ecommerce.special_percentage_charge', $config['special_percentage_charge']);
        }
        if (isset($config['delivery_expenses_type'])) {
            $container->setParameter('ecommerce.delivery_expenses_type', $config['delivery_expenses_type']);
        }
        if (isset($config['delivery_expenses_percentage'])) {
            $container->setParameter('ecommerce.delivery_expenses_percentage', $config['delivery_expenses_percentage']);
        }
        if (isset($config['bank_account'])) {
            $container->setParameter('ecommerce.bank_account', $config['bank_account']);
        }
    }
    
}
