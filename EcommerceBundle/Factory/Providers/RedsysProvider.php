<?php
namespace EcommerceBundle\Factory\Providers;

/**
 * Description of Redsys
 *
 * @author sebastian
 */
class RedsysProvider {
        
    protected $host;
    
    protected $secret;
    
    protected $currency;
    
    protected $code;
    
    protected $terminal;
    
    protected $transactionType;
    
    protected $bankResponseUrl;
    
    protected $consumerLanguage;
    
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
    
    public function setSecret($secret) 
    {
        $this->secret = $secret;
    }
    
    public function getSecret() 
    {
        return $this->secret;
    }
    
    public function setCurrency($currency) 
    {
        $this->currency = $currency;
    }
    
    public function getCurrency() 
    {
        return $this->currency;
    }
    
    public function setCode($code) 
    {
        $this->code = $code;
    }
    
    public function getCode() 
    {
        return $this->code;
    }
    
    public function setTerminal($terminal) 
    {
        $this->terminal = $terminal;
    }
    
    public function getTerminal() 
    {
        return $this->terminal;
    }
    
    public function setTransactionType($transactionType) 
    {
        $this->transactionType = $transactionType;
    }
    
    public function getTransactionType() 
    {
        return $this->transactionType;
    }
    
    public function setBankResponseUrl($bankResponseUrl) 
    {
        $this->bankResponseUrl = $bankResponseUrl;
    }
    
    public function getBankResponseUrl() 
    {
        return $this->bankResponseUrl;
    }
    
    public function setConsumerLanguage($consumerLanguage) 
    {
        $this->consumerLanguage = $consumerLanguage;
    }
    
    public function getConsumerLanguage() 
    {
        return $this->consumerLanguage;
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
