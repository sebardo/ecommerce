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
        
        //braintree
        if (isset($config['providers']['braintree_'.$env]['options']['environment'])) {
            $container->setParameter('braintree.environment', $config['providers']['braintree_'.$env]['options']['environment']);
            Braintree_Configuration::environment($config['providers']['braintree_'.$env]['options']['environment']);
        }
        if (isset($config['providers']['braintree_'.$env]['options']['merchant_id'])) {
            $container->setParameter('braintree.merchant_id', $config['providers']['braintree_'.$env]['options']['merchant_id']);
            Braintree_Configuration::merchantId($config['providers']['braintree_'.$env]['options']['merchant_id']);
        }
        if (isset($config['providers']['braintree_'.$env]['options']['public_key'])) {
            $container->setParameter('braintree.public_key', $config['providers']['braintree_'.$env]['options']['public_key']);
            Braintree_Configuration::publicKey($config['providers']['braintree_'.$env]['options']['public_key']);
        }
        if (isset($config['providers']['braintree_'.$env]['options']['private_key'])) {
            $container->setParameter('braintree.private_key', $config['providers']['braintree_'.$env]['options']['private_key']);
            Braintree_Configuration::privateKey($config['providers']['braintree_'.$env]['options']['private_key']);
        }
        
        //paypal
        if (isset($config['providers']['paypal_'.$env]['options']['host'])) {
            $container->setParameter('paypal.host', $config['providers']['paypal_'.$env]['options']['host']);
        }
        if (isset($config['providers']['paypal_'.$env]['options']['client_id'])) {
            $container->setParameter('paypal.client_id', $config['providers']['paypal_'.$env]['options']['client_id']);
        }
        if (isset($config['providers']['paypal_'.$env]['options']['secret'])) {
            $container->setParameter('paypal.secret', $config['providers']['paypal_'.$env]['options']['secret']);
        }
        if (isset($config['providers']['paypal_'.$env]['options']['return_url'])) {
            $container->setParameter('paypal.return_url', $config['providers']['paypal_'.$env]['options']['return_url']);
        }
        if (isset($config['providers']['paypal_'.$env]['options']['cancel_url'])) {
            $container->setParameter('paypal.cancel_url', $config['providers']['paypal_'.$env]['options']['cancel_url']);
        }
        
        //redsys
        if (isset($config['providers']['redsys_'.$env]['options']['host'])) {
            $container->setParameter('redsys.host', $config['providers']['redsys_'.$env]['options']['host']);
        }
        if (isset($config['providers']['redsys_'.$env]['options']['currency'])) {
            $container->setParameter('redsys.currency', $config['providers']['redsys_'.$env]['options']['currency']);
        }
        if (isset($config['providers']['redsys_'.$env]['options']['secret'])) {
            $container->setParameter('redsys.secret', $config['providers']['redsys_'.$env]['options']['secret']);
        }
        if (isset($config['providers']['redsys_'.$env]['options']['code'])) {
            $container->setParameter('redsys.code', $config['providers']['redsys_'.$env]['options']['code']);
        }
        if (isset($config['providers']['redsys_'.$env]['options']['terminal'])) {
            $container->setParameter('redsys.terminal', $config['providers']['redsys_'.$env]['options']['terminal']);
        }
        if (isset($config['providers']['redsys_'.$env]['options']['transaction_type'])) {
            $container->setParameter('redsys.transaction_type', $config['providers']['redsys_'.$env]['options']['transaction_type']);
        }
        if (isset($config['providers']['redsys_'.$env]['options']['bank_response_url'])) {
            $container->setParameter('redsys.bank_response_url', $config['providers']['redsys_'.$env]['options']['bank_response_url']);
        }
        if (isset($config['providers']['redsys_'.$env]['options']['consumer_language'])) {
            $container->setParameter('redsys.consumer_language', $config['providers']['redsys_'.$env]['options']['consumer_language']);
        }
        if (isset($config['providers']['redsys_'.$env]['options']['return_url'])) {
            $container->setParameter('redsys.return_url', $config['providers']['redsys_'.$env]['options']['return_url']);
        }
        if (isset($config['providers']['redsys_'.$env]['options']['cancel_url'])) {
            $container->setParameter('redsys.cancel_url', $config['providers']['redsys_'.$env]['options']['cancel_url']);
        }
        
    }
    
  
}
