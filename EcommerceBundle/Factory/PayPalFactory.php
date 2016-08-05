<?php
namespace EcommerceBundle\Factory;

/**
 * Factory for creating Paypal services
 */
class PayPalFactory
{
    /**
     * @var PayPal provider
     */
    protected $provider;
    
    /**
     * Constructor with PayPal configuration
     *
     * @param string $host
     * @param string $clientId
     * @param string $secret
     * @param string $returnUrl
     * @param string $cancelUrl
     */
    public function __construct($host, $clientId, $secret, $returnUrl, $cancelUrl)
    {
        $provider = new Providers\PayPalProvider();
        $provider->setHost($host);
        $provider->setClientId($clientId);
        $provider->setSecret($secret);
        $provider->setReturnUrl($returnUrl);
        $provider->setCancelUrl($cancelUrl);
        $this->provider = $provider;
    }

    /**
     * Get provider 
     *
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }
}
