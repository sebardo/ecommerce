<?php

namespace EcommerceBundle\Factory;

use EcommerceBundle\Exception\InvalidServiceException;
use Braintree_Configuration;

/**
 * Factory for creating Braintree services
 */
class BraintreeFactory
{
    /**
     * Constructor with Braintree configuration
     *
     * @param string $environment
     * @param string $merchantId
     * @param string $publicKey
     * @param string $privateKey
     */
    public function __construct($environment, $merchantId, $publicKey, $privateKey)
    {
        Braintree_Configuration::environment($environment);
        Braintree_Configuration::merchantId($merchantId);
        Braintree_Configuration::publicKey($publicKey);
        Braintree_Configuration::privateKey($privateKey);
    }

    /**
     * Factory method for creating and getting Braintree services
     *
     * @param string $serviceName braintree service name
     * @param array $attributes   attribures for braintree service creation
     *
     * @return mixed
     */
    public function get($serviceName, array $attributes = array(), $methodName='factory')
    {
        $className = 'Braintree_' . ucfirst($serviceName);
        if(class_exists($className) && method_exists($className, $methodName)) {
            if($methodName=='factory') return $className::$methodName($attributes);
            else return $className::$methodName();
        } else {
            throw new InvalidServiceException('Invalid service ' . $serviceName);
        }
    }
}
