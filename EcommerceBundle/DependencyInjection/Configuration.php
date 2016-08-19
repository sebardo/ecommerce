<?php

namespace EcommerceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ecommerce');

        $rootNode
            ->children()
                ->booleanNode('fixture_data')->defaultTrue()->end()
                ->scalarNode('currency_symbol')->defaultValue('€')->end()
                ->scalarNode('advert_unit_price')->defaultValue('1')->end()
                ->scalarNode('vat')->defaultValue('0.21')->end()
                ->scalarNode('special_percentage_charge')->defaultValue('0')->end()
                ->scalarNode('delivery_expenses_type')->defaultValue('by_percentage')->end()
                ->scalarNode('delivery_expenses_percentage')->defaultValue('0')->end()
                ->scalarNode('bank_account')->defaultValue('ESXX XXX XXXX XXXX XXXX XXXX')->end()
                
            ->end();
        return $treeBuilder;
    }
}
