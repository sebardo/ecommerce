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
        if (isset($config['fixtures_dev'])) {
            $container->setParameter('ecommerce.fixtures_dev', $config['fixtures_dev']);
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
        //company
        if (isset($config['company']) && isset($config['company']['id'])) {
            $container->setParameter('ecommerce.company.id', $config['company']['id']);
        }
        if (isset($config['company']) && isset($config['company']['name'])) {
            $container->setParameter('ecommerce.company.name', $config['company']['name']);
        }
        if (isset($config['company']) && isset($config['company']['address'])) {
            $container->setParameter('ecommerce.company.address', $config['company']['address']);
        }
        if (isset($config['company']) && isset($config['company']['postal_code'])) {
            $container->setParameter('ecommerce.company.postal_code', $config['company']['postal_code']);
        }
        if (isset($config['company']) && isset($config['company']['city'])) {
            $container->setParameter('ecommerce.company.city', $config['company']['city']);
        }
        if (isset($config['company']) && isset($config['company']['country'])) {
            $container->setParameter('ecommerce.company.country', $config['company']['country']);
        }
        if (isset($config['company']) && isset($config['company']['telephone'])) {
            $container->setParameter('ecommerce.company.telephone', $config['company']['telephone']);
        }
        if (isset($config['company']) && isset($config['company']['email'])) {
            $container->setParameter('ecommerce.company.email', $config['company']['email']);
        }
        if (isset($config['company']) && isset($config['company']['sales_email'])) {
            $container->setParameter('ecommerce.company.sales_email', $config['company']['sales_email']);
        }
        if (isset($config['company']) && isset($config['company']['website_url'])) {
            $container->setParameter('ecommerce.company.website_url', $config['company']['website_url']);
        }
        if (isset($config['company']) && isset($config['company']['support_phone'])) {
            $container->setParameter('ecommerce.company.support_phone', $config['company']['support_phone']);
        }
        if (isset($config['company']) && isset($config['company']['sales_phone'])) {
            $container->setParameter('ecommerce.company.sales_phone', $config['company']['sales_phone']);
        }
        if (isset($config['company']) && isset($config['company']['sales_fax'])) {
            $container->setParameter('ecommerce.company.sales_fax', $config['company']['sales_fax']);
        }

                
    }
    
}
