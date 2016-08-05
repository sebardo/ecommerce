<?php
namespace EcommerceBundle\Factory\Providers;

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
    
class PayPalProvider {

    protected $host;
    
    protected $clientId;
    
    protected $secret;
    
    protected $returnUrl;
    
    protected $cancelUrl;
 
    
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
        
}
