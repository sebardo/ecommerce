<?php
namespace EcommerceBundle\Factory\Providers;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Regex;
use EcommerceBundle\Factory\PaymentProviderFactory;
use EcommerceBundle\Entity\PaymentServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use EcommerceBundle\Entity\Transaction;
use EcommerceBundle\Entity\Delivery;
use EcommerceBundle\Entity\Product;
use EcommerceBundle\Entity\Advert;
use stdClass;

/**
 * Validation control
 * @Assert\Callback(methods={"constraintValidation","checkExpirationDate"})
**/
class PayPalDirectPaymentProvider  extends PaymentProviderFactory 
{
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
    protected $host;
    
    protected $clientId;
    
    protected $secret;
    
    protected $returnUrl;
    
    protected $cancelUrl;
    
    protected $token;
    
    public $plan;
    
    public $firstname;

    public $lastname;

    public $creditcardAlias;

    public $cardType;

    public $cardNo;

    public $expirationDate;

    public $CVV;

    public $ts;
 

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
    
        
    /**
     * Checks if the expiration date is valid
     * @param  ExecutionContext $context
     * @return void
     */
    public function checkExpirationDate(ExecutionContext $context)
    {
        $expirationDate = $this->expirationDate;

        if (is_null($expirationDate)) {
            $context->addViolationAt("expirationDate", "La fecha de vencimiento es invalida", array(), null);
        } else {
            $ts = $expirationDate->format('U');
            if ($ts<=time()) {
                $context->addViolationAt("expirationDate", "La fecha de vencimiento es invalida", array(), null);
            }
        }

    }

    public function constraintValidation(ExecutionContext $context)
    {
        $customNotBlankConstraint = new NotBlank();
        $customNotBlankConstraint->message = "Este valor no puede estar vacio";

        $customLengthConstraint = new Length(16);
        $customLengthConstraint->minMessage = "cardNoMinLength";
        $customLengthConstraint->maxMessage = "cardNoMaxLength";

        $collectionConstraint = new Collection(array(
            "firstname"=> array(
                                    $customNotBlankConstraint,
                                ),
            "lastname"=> array(
                                    $customNotBlankConstraint,
                                ),
            "cardNo" => array(
                                new Regex("/^\d+$/"),
                                $customNotBlankConstraint,
                                $customLengthConstraint,
                                ),
            "cardType"=> array(
                                $customNotBlankConstraint,
                                ),
            "CVV" => array  (
                                $customNotBlankConstraint,
                             ),

            ));

        /**
         * validateValue expects either a scalar value and its constraint or an array and a constraint Collection
         */
        $errors = $this->validator->validateValue(array(
            "firstname" => $this->firstname,
            "lastname" => $this->lastname,
            "cardNo" => $this->cardNo,
            "cardType" => $this->cardType,
            "CVV" => $this->CVV,

        ), $collectionConstraint);

        /**
         * Count is used as this is not an array but a ConstraintViolationList
         */
        if (count($errors) !== 0) {
            $path = $context->getPropertyPath();
            foreach ($errors as $error) {
               $string = str_replace('[', '', $error->getPropertyPath());
               $string = str_replace(']', '', $string);
               $propertyPath = $path . '.'.$string;
               $context->addViolationAt($string, $error->getMessage(), array(), null);
            }
        }
    }
    
    public function getToken() {
        # Token Sandbox
        $host = $this->getHost();
        $url = $host.'/v1/oauth2/token'; 
        $postdata = 'grant_type=client_credentials';
        
        $curl = curl_init($url); 
        curl_setopt($curl, CURLOPT_POST, true); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_USERPWD, $this->getClientId(). ":" . $this->getSecret());
        curl_setopt($curl, CURLOPT_HEADER, false); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata); 
        $response = curl_exec( $curl );
        if (empty($response)) {
            // some kind of an error happened
            die(curl_error($curl));
            curl_close($curl); // close cURL handler
        } else {
            $info = curl_getinfo($curl);
            curl_close($curl); // close cURL handler
                if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
                        echo "Received error: " . $info['http_code']. "\n";
                        echo "Raw response:".$response."\n";
                        die(curl_error($curl));
            }
        }
        // Convert the result from JSON format to a PHP array 
        $jsonResponse = json_decode( $response );
        $this->token = $jsonResponse->access_token;
    }
    
    public function call($method, $url, $postdata=null) 
    {
        if($method == 'GET')
        {
            $curl = curl_init($url); 
            curl_setopt($curl, CURLOPT_POST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                                    'Authorization: Bearer '.$this->token,
                                    'Accept: application/json',
                                    'Content-Type: application/json'
                                    ));

            #curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
            $response = curl_exec( $curl );
            if (empty($response)) {
                // some kind of an error happened
                curl_close($curl); // close cURL handler
                return array(
                        'status' => 'error',
                        'error' => curl_error($curl)
                    );
            } else {
                
                $info = curl_getinfo($curl);
                curl_close($curl); // close cURL handler
                if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
                    return array(
                        'status' => 'error',
                        'error' => json_decode($response, TRUE)
                    );

                }
            }
            // Convert the result from JSON format to a PHP array 
            $jsonResponse = json_decode($response, TRUE);
            return $jsonResponse;
        }
        elseif($method == 'POST')
        {
            $curl = curl_init(); 
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                                    'Authorization: Bearer '.$this->token,
                                    'Accept: application/json',
                                    'Content-Type: application/json'
                                    ));

            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata); 
            #curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
            $response = curl_exec( $curl );
            if (empty($response)) {
                // some kind of an error happened
//                curl_close($curl); // close cURL handler
                return array(
                        'status' => 'error',
                        'error' => curl_error($curl)
                    );
            } else {
                $info = curl_getinfo($curl);
//                var_dump($response);die();
                curl_close($curl); // close cURL handler
                if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
                    return array(
                        'status' => 'error',
                        'error' => $response,
                    );
                }
            }
            // Convert the result from JSON format to a PHP array 
            $jsonResponse = json_decode($response, TRUE);
            return $jsonResponse;
        }
        elseif($method == 'PATCH')
        {
            $curl = curl_init(); 
            //set connection properties
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER,array(
                                    'Authorization: Bearer '.$this->token,
                                    'Content-Type: application/json'
                                    ));
            //curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
 
            #curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
            $response = curl_exec( $curl );
            if (empty($response)) {
                // some kind of an error happened
                die(curl_error($curl));
                curl_close($curl); // close cURL handler
            } else {
                $info = curl_getinfo($curl);
                curl_close($curl); // close cURL handler
                if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
                    return array(
                        'status' => 'error',
                        'error' => json_decode($response, TRUE)
                    );
                }
            }
            // Convert the result from JSON format to a PHP array 
            $jsonResponse = json_decode($response, TRUE);
            return $jsonResponse;
        }
    }
    
    /**
     * Proccess sale transaction
     *
     * @param Request $request
     * @param Transaction $transaction
     * @param Delivery $delivery
     *
     * @return stdClass
     */
    public function process(Request $request, Transaction $transaction, Delivery $delivery)
    {
        $data = $request->get('pay_pal_direct_payment');
        
        $creditCard = array(
            "number" => $data['cardNo'],
            "type" => $data['cardType'],
            "expire_month" =>  $data['expirationDate']['month'],
            "expire_year" =>  $data['expirationDate']['year'],
            "cvv2" =>  $data['CVV'],
            "first_name" =>  $data['firstname'],
            "last_name" =>  $data['lastname']
       );
        $totals = $this->container->get('checkoutManager')->calculateTotals($transaction, $delivery);
  
        $this->getToken();
        
        $paymentMehod = array(
        "payment_method" => "credit_card",
        "funding_instruments" => array(
            array(
              "credit_card" => $creditCard
            )
          )
        );
        
        
        $returnValues = array();
        foreach ($transaction->getItems() as $productPurchase) {
            $sub = array();
            $sub['quantity'] = $productPurchase->getQuantity();
            if($productPurchase->getProduct() instanceof Product){
                $sub['name'] = $productPurchase->getProduct()->getName();
                $sub['price'] = number_format($productPurchase->getProduct()->getPrice(), 2);
            }elseif($productPurchase->getAdvert() instanceof Advert){
                $sub['name'] = $productPurchase->getAdvert()->getTitle();
                $sub['price'] = number_format($this->advertUnitPrice, 2);
            }
            
            
            $sub['sku'] = $productPurchase->getId();
            $sub['currency'] = "EUR";
            $returnValues[] = $sub;
        }
        
        $host = $this->getHost();
        $url = $host.'/v1/payments/payment';
        $payment = array(
                        'intent' => 'sale',
                        'payer' => $paymentMehod,
                        'transactions' => array (array(
                                        'amount' => array(
                                                'total' => number_format($totals['amount'], 2),
                                                'currency' => 'EUR',
                                                'details' =>  array(
                                                    "subtotal" => number_format($totals['amount_clean'], 2),
                                                    "tax" => number_format($totals['vat'], 2),
                                                    "shipping" => $totals['delivery_expenses']
                                                  )
                                                ),
                                        'description' => 'payment using a PayPal account',
                                        "item_list" => array (
                                                "items" => $returnValues
                                                ),
                                        )),
                        'redirect_urls' => array (
                                'return_url' => $this->getReturnUrl(),
                                'cancel_url' => $this->getCancelUrl()
                        )
                    );

        $json = json_encode($payment);
        $json_resp = $this->call('POST', $url, $json);

        $em = $this->container->get('doctrine')->getManager();
        if($json_resp['state'] == 'approved'){
             //UPDATE TRANSACTION
            $pm = $em->getRepository('EcommerceBundle:PaymentMethod')->findOneBySlug('paypal-direct-payment');
            $transaction->setStatus(Transaction::STATUS_PAID);
            $transaction->setPaymentMethod($pm);
            $transaction->setPaymentDetails(json_encode($json_resp));
            $em->persist($transaction);
            $em->flush();

            //confirmation payment
            $answer = new stdClass();
            $answer->redirectUrl = $this->container->get('router')->generate('ecommerce_checkout_confirmationpayment');

            return $answer;
        }else{
            //cancel payment
            $answer = new stdClass();
            $answer->redirectUrl = $this->getCancelUrl();

            return $answer;
        }
        
    }
}
