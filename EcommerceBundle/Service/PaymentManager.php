<?php
namespace EcommerceBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use EcommerceBundle\Factory\PaymentProviderFactory;
use Symfony\Component\HttpFoundation\Request;
use EcommerceBundle\Entity\Transaction;
use EcommerceBundle\Entity\Delivery;
use EcommerceBundle\Entity\PaymentServiceProvider;

/**
 * Description of PaymentManager
 *
 * @author sebastian
 */
class PaymentManager 
{
    protected $container;
    
    protected $providers;
    
    public function setContainer($container) {
        $this->container = $container;
        $env = $this->getContainer()->getParameter("kernel.environment");
        $isTest = ($env == 'test' || $env == 'dev') ? true : false;
        /** @var array PaymentServiceProvider */
        $psps = $this->getContainer()->get('doctrine')->getManager()->getRepository('EcommerceBundle:PaymentServiceProvider')->findBy(array(
            'isTestingAccount' => $isTest,
            'active' => true
        ), array('id' => 'DESC'));
         $returnValues = new ArrayCollection();
        foreach ($psps as $psp) {
            $class = $psp->getModelClass();
            $pp = new $class($this->getContainer()->get('validator'));
            $returnValues[] = $pp->initialize($this->getContainer(), $psp);
        }
        
        $this->providers = $returnValues;
        
    }
    
    public function getContainer() {
        return $this->container;
    }
    
    /**
     * Set ArrayCollection of PaymentProviderFactory
     * 
     * @return ArrayCollection $providers
     */
    public function setProviders($providers) 
    {
        $this->providers = $providers;
    }
    
    /**
     * Return ArrayCollection of PaymentProviderFactory
     * 
     * @return PersistCollection $psps
     */
    public function getProviders(PaymentServiceProvider $psp=null) 
    {
        if(!is_null($psp)){
            foreach ($this->providers as $ppf) {
                if($ppf->getSlug() == $psp->getSlug()){
                    return $ppf;
                }
            }
        }
        return $this->providers;
    }
    
    public function processPayment(Request $request, Transaction $transaction, Delivery $delivery, PaymentProviderFactory $ppf) 
    {
        return $ppf->process($request, $transaction, $delivery);
    }
    
    public function confirmationPayment(Request $request, PaymentServiceProvider $psp) 
    {
        foreach ($this->providers as $ppf) {
            if($ppf->getSlug() == $psp->getPaymentMethod()->getSlug()){
                return $ppf->confirmation($request);
            }
        }
        throw new \Exception('No confirmation method defined');
    }
    
    public function cancelationPayment(Request $request, PaymentServiceProvider $psp) 
    {
        foreach ($this->providers as $ppf) {
            if($ppf->getSlug() == $psp->getPaymentMethod()->getSlug()){
                return $ppf->cancelation($request);
            }
        }
        throw new \Exception('No cancelation method defined');
        
    }
}
