<?php
namespace EcommerceBundle\Factory;

/**
 * Factory for creating Redsys services
 */
class RedsysFactory
{
    /**
     * @var Redsys provider
     */
    protected $provider;
    
    /**
     * Constructor with Redsys configuration
     *
     * @param string $host
     * @param string $secret
     * @param string $currency
     * @param string $code
     * @param string $terminal
     * @param string $transactionType
     * @param string $bankResponseUrl
     * @param string $consumerLanguage
     * @param string $returnUrl
     * @param string $cancelUrl
     */
    public function __construct(
            $host, 
            $secret, 
            $currency, 
            $code, 
            $terminal, 
            $transactionType, 
            $bankResponseUrl, 
            $consumerLanguage, 
            $returnUrl, 
            $cancelUrl
            )
    {
        $provider = new Providers\RedsysProvider();
        $provider->setHost($host);
        $provider->setSecret($secret);
        $provider->setCurrency($currency);
        $provider->setCode($code);
        $provider->setTerminal($terminal);
        $provider->setTransactionType($transactionType);
        $provider->setBankResponseUrl($bankResponseUrl);
        $provider->setConsumerLanguage($consumerLanguage);
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
