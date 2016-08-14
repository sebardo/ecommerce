<?php
namespace EcommerceBundle\Factory\Providers;

use EcommerceBundle\Factory\PaymentProviderFactory;
use EcommerceBundle\Entity\PaymentServiceProvider;

/**
 * Description of PayPal
 *
 * @author sebastian
 * TEST
 * Utilice esta tarjeta de prueba:
 * Número de tarjeta: 4548812049400004
 * Caducidad: 12/17 
 * Código CVV2: 123 
 * Código CIP: 123456 
 */
    
class PayPalProvider  extends PaymentProviderFactory 
{

    protected $host;
    
    protected $clientId;
    
    protected $secret;
    
    protected $returnUrl;
    
    protected $cancelUrl;
    
    protected $paypal;
 
    /**
     * Constructor with Paypal configuration
     *
     * @param string $container
     * @param PaymentServiceProvider $psp
     */
    public function initialize($container, PaymentServiceProvider $psp)
    {
        parent::initialize($container, $psp);
        if(isset($this->parameters['host'])) $this->setHost($this->parameters['host']);
        if(isset($this->parameters['client_id'])) $this->setClientId($this->parameters['client_id']);
        if(isset($this->parameters['secret'])) $this->setSecret($this->parameters['secret']);
        if(isset($this->parameters['return_url'])) $this->setReturnUrl($this->parameters['return_url']);
        if(isset($this->parameters['cancel_url'])) $this->setCancelUrl($this->parameters['cancel_url']);
        
        return $this;
    }
    
    public function setHost($host) 
    {
        $this->host = $host;
    }
    
    public function getHost() 
    {
        return $this->host;
    }
    
    public function setClientId($clientId) 
    {
        $this->clientId = $clientId;
    }
    
    public function getClientId() 
    {
        return $this->clientId;
    }
    
    public function setSecret($secret) 
    {
        $this->secret = $secret;
    }
    
    public function getSecret() 
    {
        return $this->secret;
    }
    
    public function setReturnUrl($returnUrl) 
    {
        $this->returnUrl = $returnUrl;
    }
    
    public function getReturnUrl() 
    {
        return $this->returnUrl;
    }
    
    public function setCancelUrl($cancelUrl) 
    {
        $this->cancelUrl = $cancelUrl;
    }
    
    public function getCancelUrl() 
    {
        return $this->cancelUrl;
    }
    
    public function setPaypal($paypal) 
    {
        $this->paypal = $paypal;
    }
    
    public function getPaypal() 
    {
        return $this->paypal;
    }
        
}
