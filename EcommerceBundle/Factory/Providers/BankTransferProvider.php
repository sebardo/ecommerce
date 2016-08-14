<?php
namespace EcommerceBundle\Factory\Providers;

use EcommerceBundle\Factory\PaymentProviderFactory;
use EcommerceBundle\Entity\PaymentServiceProvider;


class BankTransferProvider  extends PaymentProviderFactory 
{

    protected $bankTransfer;
    
    /**
     * Constructor with Paypal configuration
     *
     * @param string $container
     * @param PaymentServiceProvider $psp
     */
    public function initialize($container, PaymentServiceProvider $psp)
    {
        parent::initialize($container, $psp);
        
        return $this;
    }
   
    public function getBankTransfer() {
        return $this->bankTransfer;
    }
    
    public function setBankTransfer($bankTransfer) {
        $this->bankTransfer = $bankTransfer;
    }
        
}
