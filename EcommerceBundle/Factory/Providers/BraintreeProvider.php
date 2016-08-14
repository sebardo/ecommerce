<?php
namespace EcommerceBundle\Factory\Providers;

use EcommerceBundle\Factory\PaymentProviderFactory;
use Braintree_Configuration;
use Symfony\Component\HttpFoundation\Request;
use EcommerceBundle\Entity\Transaction;
use EcommerceBundle\Entity\Delivery;
use EcommerceBundle\Entity\PaymentServiceProvider;

/**
 * Description of BraintreeProvider
 *
 * @author sebastian
 */
class BraintreeProvider extends PaymentProviderFactory 
{
    
//    protected $validator;

    protected $payment_method_nonce;
    
    /**
     * Constructor with Braintree configuration
     *
     * @param string $container
     * @param PaymentServiceProvider $psp
     */
    public function initialize($container, PaymentServiceProvider $psp)
    {
        parent::initialize($container, $psp);
        print_r($this->parameters);die();
        Braintree_Configuration::environment($environment);
        Braintree_Configuration::merchantId($merchantId);
        Braintree_Configuration::publicKey($publicKey);
        Braintree_Configuration::privateKey($privateKey);
        
    }
    
    public function getPaymentMethodNonce() {
        return $this->payment_method_nonce;
    }
    
    public function setPaymentMethodNonce($payment_method_nonce) {
        $this->payment_method_nonce = $payment_method_nonce;
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
    
    /**
     * Proccess sale transaction
     *
     * @param Transaction $transaction
     * @param Delivery $delivery
     *
     * @return stdClass
     */
    public function process(Request $request, Transaction $transaction, Delivery $delivery)
    {
        // in your controller
        $transactionService = $this->get('transaction');
        $nonce = $request->get('payment_method_nonce');

        $result = $transactionService::sale([
            'amount' => $transaction->getTotalPrice(),
            'paymentMethodNonce' => $nonce
        ]);

        $pm = $this->manager->getRepository('EcommerceBundle:PaymentMethod')->findOneBySlug('braintree');
        if ($result->success || !is_null($result->transaction)) {
            //UPDATE TRANSACTION
            $transaction->setStatus(Transaction::STATUS_PAID);
            $transaction->setPaymentMethod($pm);
            
            //details
            $details = new stdClass();
            $details->id = $result->transaction->id;
            $details->status = $result->transaction->status;
            $details->type = $result->transaction->type;
            $details->currencyIsoCode = $result->transaction->currencyIsoCode;
            $details->amount = $result->transaction->amount;
            $details->merchantAccountId = $result->transaction->merchantAccountId;
            $details->createdAt = $result->transaction->createdAt;
            $details->updatedAt = $result->transaction->updatedAt;
            $details->customer = $result->transaction->customer;
            $details->billing = $result->transaction->billing;
            $details->shipping = $result->transaction->shipping;
            
            $transaction->setPaymentDetails(json_encode($details));
            $this->manager->persist($transaction);
            $this->manager->flush();

            //confirmation payment
            $answer = new \stdClass();
            $answer->redirectUrl = $this->router->generate('ecommerce_checkout_confirmationpayment');

            return $answer;
       }else{
            //UPDATE TRANSACTION
            $transaction->setStatus(Transaction::STATUS_CANCELLED);
            $transaction->setPaymentMethod($pm);
            
            //details
            $errorString = "";
            foreach($result->errors->deepAll() as $error) {
               $errorString .= 'Error: ' . $error->code . ": " . $error->message . "\n";
            }
            $this->session->getFlashBag()->add('error', $errorString);
            
            $transaction->setPaymentDetails(json_encode($errorString));
            $this->manager->persist($transaction);
            $this->manager->flush();

            //cancel payment
            $answer = new \stdClass();
            $answer->redirectUrl = $this->paypalFactory->getCancelUrl();

            return $answer;
        }
 
    }
    
}
