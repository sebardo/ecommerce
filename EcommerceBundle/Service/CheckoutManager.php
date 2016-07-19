<?php

namespace EcommerceBundle\Service;

use EcommerceBundle\Entity\Delivery;
use EcommerceBundle\Entity\Transaction;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use EcommerceBundle\Entity\ProductPurchase;
use EcommerceBundle\Entity\Cart;
use CoreBundle\Entity\Actor;
use EcommerceBundle\Entity\Address;
use EcommerceBundle\Entity\Invoice;
use EcommerceBundle\Entity\CartItem;
use Symfony\Component\HttpFoundation\Request;
use CoreBundle\Entity\State;
use EcommerceBundle\Entity\Plan;
use EcommerceBundle\Entity\Agreement;
use EcommerceBundle\Entity\Product;
use EcommerceBundle\Entity\Advert;

use DateTime;

/**
 * Class CheckoutManager
 */
class CheckoutManager
{
//    TEST
//    Utilice esta tarjeta de prueba:
//    Número de tarjeta: 4548812049400004
//    Caducidad: 12/17 
//    Código CVV2: 123 
//    Código CIP: 123456 
    
    private $parameters;

    private $session;
    
    private $manager;
    
    private $cartProvider;
    
    private $securityContext;
    
    private $router;
    
    private $kernel;
    
    private $environment;

    public  $token;
    
    public $mailer;
        
    /**
     * @param array $parameters
     */
    public function __construct(
            array $parameters, 
            $session, 
            $manager, 
            $cartProvider, 
            $securityContext,
            $router,
            $kernel,
            $mailer
            )
    {
        if(isset($parameters['parameters'])) $this->parameters = $parameters['parameters'];
        $this->session = $session;
        $this->manager = $manager;
        $this->cartProvider = $cartProvider;
        $this->securityContext = $securityContext;
        $this->router = $router;
        $this->kernel = $kernel;
        $this->environment = $kernel->getEnvironment();
        $this->mailer = $mailer;     
    }
    
     /**
     * Calculate totals
     *
     * @param Order    $transaction
     * @param Delivery $delivery
     *
     * @return array
     */
    public function calculateTotals(Transaction $transaction, $delivery=null)
    {
        if($transaction->getItems()->first()->getAdvert() instanceof  Advert){
            return $this->calculateTotalsAdvert($transaction);
        }
        $totals['delivery_expenses'] = 0;
        $totals['vat'] = $this->parameters['company']['vat'];
        
        if($transaction->getPaymentMethod() == Transaction::PAYMENT_METHOD_STORE_PICKUP){
            $totals['amount'] = $transaction->getTotalPrice();
            $totals['amount_clean'] = $transaction->getTotalPrice();
            $totals['delivery_expenses'] = 0;
            $totals['vat'] = 0;
            // return total
            return $totals;
        }
        
        $totals['amount'] = $transaction->getTotalPrice();
        $totals['amount_clean'] = $transaction->getTotalPrice();
        if(!is_null($delivery)){
            $totalPerDeliveryExpenses = $this->calculateTotalAmountForDeliveryExpenses($transaction);

            // calculate delivery expenses
            if ('by_percentage' === $this->parameters['company']['delivery_expenses_type']) {
                $totals['delivery_expenses'] = round($totalPerDeliveryExpenses * ($delivery->getExpenses() / 100),2);
            } else {
                $totals['delivery_expenses'] = $delivery->getExpenses();
            }
        }
        
        // calculate vat
        if(!is_null($transaction->getVat())){
            $vat = $transaction->getVat();
            $totals['vat'] = ($totals['amount'] + $totals['delivery_expenses']) * ($vat / 100);
            // calculate amount
            $totals['amount'] += $totals['delivery_expenses'] + $totals['vat'];
        }else{
             $totals['amount'] += $totals['delivery_expenses'];
        }
 
        // return total
        return $totals;
    }
    
     /**
     * Calculate totals
     *
     * @param Order    $transaction
     * @param Delivery $delivery
     *
     * @return array
     */
    public function calculateTotalsAdvert(Transaction $transaction, $delivery=null)
    {
        $totals['delivery_expenses'] = 0;
        $totals['amount'] = $transaction->getTotalPrice();
        $totals['amount_clean'] = $transaction->getItems()->first()->getTotalPrice();
        $totals['vat'] = $totals['amount_clean'] * ($transaction->getVat() / 100);
       
        // return total
        return $totals;
    }
    
    /**
     * Calculate total amount for delivery expenses
     *
     * @param Transaction    $transaction
     *
     * @return float $total
     */
    private function calculateTotalAmountForDeliveryExpenses(Transaction $transaction)
    {
        $total = 0;
        foreach ($transaction->getItems() as $productPurchase) {
            if($productPurchase->getProduct() instanceof Product){
                if(!$productPurchase->getProduct()->isFreeTransport()){
                    $total += $productPurchase->getTotalPrice();
                }
            }else{
                $total += $productPurchase->getTotalPrice();
            }
        }
        return $total;
    }
    
    /**
     * Get current transaction
     *
     * @throws AccessDeniedHttpException
     * @return Transaction
     */
    public function getCurrentTransaction()
    {
        if (false === $this->session->has('transaction-id')) {
            throw new AccessDeniedHttpException();
        }

        return $this->manager->getRepository('EcommerceBundle:Transaction')->find($this->session->get('transaction-id'));
    }
    
    /**
     * Update transaction with cart's contents
     */
    public function updateTransaction()
    {
//        print_r($this->parameters);die();
        
        $cart = $this->cartProvider->getCart();

        if (0 === $cart->countItems() || $this->isTransactionUpdated($cart)) {
            return;
        }

        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->manager->getRepository('EcommerceBundle:Transaction');

        if ($this->session->has('transaction-id')) {
            /** @var Transaction $transaction */
            $transaction = $transactionRepository->find($this->session->get('transaction-id'));

            $transactionRepository->removeItems($transaction);
        } else {
            $transactionKey = $transactionRepository->getNextNumber();

            // create a new transaction
            $transaction = new Transaction();
            $transaction->setTransactionKey($transactionKey);
            $transaction->setStatus(Transaction::STATUS_CREATED);
            $transaction->setActor($this->securityContext->getToken()->getUser());
            $cartItem = $cart->getItems()->first();
            $product = $cartItem->getProduct();
        }

        $orderTotalPrice = 0;

        foreach ($cart->getItems() as $cartItem) {
            /** @var Product $product */
            $product = $cartItem->getProduct();

            $productPurchase = new ProductPurchase();
            $productPurchase->setProduct($product);
            $productPurchase->setBasePrice($cartItem->getUnitPrice());
            $productPurchase->setQuantity($cartItem->getQuantity());
            $productPurchase->setDiscount($product->getDiscount());
            $productPurchase->setTotalPrice($cartItem->getTotal());
            $productPurchase->setTransaction($transaction);
            $productPurchase->setReturned(false);
            //free transport
            if($cartItem->isFreeTransport()){
                $productPurchase->setDeliveryExpenses(0);
            }else{ 
                $productPurchase->setDeliveryExpenses($cartItem->getShippingCost());
            }
            $orderTotalPrice += $cartItem->getProduct()->getPrice() * $cartItem->getQuantity();

            $this->manager->persist($productPurchase);
        }

        $transaction->setTotalPrice($orderTotalPrice);

        $this->manager->persist($transaction);
        $this->manager->flush();

        $this->session->set('transaction-id', $transaction->getId());
        $this->session->save();
        
    }
    
    public function createAdvertTransaction($advert, $unitPrice, $quantity, $discount, $subtotal, $totalPrice) 
    {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->manager->getRepository('EcommerceBundle:Transaction');

        // create a new transaction
        $transactionKey = $transactionRepository->getNextNumber();
        $transaction = new Transaction();
        $transaction->setTransactionKey($transactionKey);
        $transaction->setStatus(Transaction::STATUS_CREATED);
        if($advert->getActor() instanceof Actor) $transaction->setActor($advert->getActor());
        $transaction->setVat(21);
        $transaction->setTotalPrice($totalPrice);
 
        // create a new productpurchase
        $productPurchase = new ProductPurchase();
        $productPurchase->setAdvert($advert);
        $productPurchase->setBasePrice($unitPrice);
        $productPurchase->setQuantity($quantity);
        $productPurchase->setDiscount($discount);
        $productPurchase->setTotalPrice($subtotal);
        $productPurchase->setTransaction($transaction);
        $productPurchase->setReturned(false);
        $productPurchase->setDeliveryExpenses(0);
        
        $this->manager->persist($productPurchase);
        $transaction->addItem($productPurchase);
        $this->manager->persist($transaction);
        $this->manager->flush();
        
        $this->createInvoice(null, $transaction);
        
        return $transaction;
    }
    
    public function createAdvertTransactionFront($advert, $unitPrice, $quantity, $discount, $subtotal, $totalPrice) 
    {
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->manager->getRepository('EcommerceBundle:Transaction');

        // create a new transaction
        $transactionKey = $transactionRepository->getNextNumber();
        $transaction = new Transaction();
        $transaction->setAdvert($advert);
        $transaction->setTransactionKey($transactionKey);
        $transaction->setStatus(Transaction::STATUS_CREATED);
        $transaction->setActor($this->securityContext->getToken()->getUser());
        $transaction->setVat(21);
        $transaction->setTotalPrice($totalPrice);
 
        // create a new productpurchase
        $productPurchase = new ProductPurchase();
        $productPurchase->setAdvert($advert);
        $productPurchase->setBasePrice($unitPrice);
        $productPurchase->setQuantity($quantity);
        $productPurchase->setDiscount($discount);
        $productPurchase->setTotalPrice($subtotal);
        $productPurchase->setTransaction($transaction);
        $productPurchase->setReturned(false);
        $productPurchase->setDeliveryExpenses(0);
        
        $this->manager->persist($productPurchase);
        $transaction->addItem($productPurchase);
        $this->manager->persist($transaction);
        $this->manager->flush();
        
        $this->createInvoice(null, $transaction);
        
        return $transaction;
    }
    /**
     * Compare current cart with current transaction
     *
     * @param CartInterface $cart
     *
     * @return boolean
     */
    private function isTransactionUpdated(Cart $cart)
    {
        if (false === $this->session->has('transaction-id')) {
            return false;
        }

        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->manager->getRepository('EcommerceBundle:Transaction');

        /** @var Order $order */
        $transaction = $transactionRepository->find($this->session->get('transaction-id'));

        /** @var ArrayCollection $cartItems */
        $cartItems = $cart->getItems();
        /** @var ArrayCollection $orderItems */
        $productPurchases = $transaction->getItems();

        if ($cartItems->count() !== $productPurchases->count()) {
            return false;
        }

        for ($i=0; $i<$cartItems->count(); $i++) {
            /** @var CartItem $cartItem */
            $cartItem = $cartItems[$i];
            /** @var OrderItem $orderItem */
            $productPurchase = $productPurchases[$i];

            if ($cartItem->getProduct()->getId() !== $productPurchase->getProduct()->getId() ||
                $cartItem->getQuantity() !== $productPurchase->getQuantity()) {
                return false;
            }
        }

        return true;
    }
    
    /**
     * Build a delivery object for the current actor
     *
     * @param Transaction $transaction
     *
     * @return Delivery
     */
    public function getDelivery(Transaction $transaction = null)
    {
        if ($this->session->has('delivery-id')) {
            $delivery = $this->manager->getRepository('EcommerceBundle:Delivery')->find($this->session->get('delivery-id'));

            return $delivery;
        }

        $delivery = new Delivery();

        /** @var Address $billingAddress */
        $billingAddress = $this->manager->getRepository('EcommerceBundle:Address')->findOneBy(array(
            'actor' => $this->securityContext->getToken()->getUser(),
            'forBilling' => true
        ));

        if (false === is_null($billingAddress)) {
            $delivery->setFullName($this->securityContext->getToken()->getUser()->getFullName());
            $delivery->setContactPerson($billingAddress->getContactPerson());
            $delivery->setDni($billingAddress->getDni());
            $delivery->setAddressInfo($billingAddress->getAddressInfo());
            $delivery->setPhone($billingAddress->getPhone());
            $delivery->setPhone2($billingAddress->getPhone2());
            $delivery->setPreferredSchedule($billingAddress->getPreferredSchedule());
        }

        $country = $this->manager->getRepository('CoreBundle:Country')->find('es');
        $delivery->setCountry($country);

        if (false === is_null($transaction)) {
            $delivery->setTransaction($transaction);
        }

        return $delivery;
    }
    
    /**
     * Save delivery fields from billing fields
     *
     * @param Delivery $delivery
     * @param array    $params
     */
    public function saveDelivery(Delivery $delivery, array $params, $cart)
    {
        /** @var Carrier $carrier */
//        $carrier = $this->manager->getRepository('ModelBundle:Carrier')->find($delivery->getCarrier());

        if ('same' === $params['selectDelivery']) {
            $delivery->setDeliveryContactPerson($delivery->getContactPerson());
            $delivery->setDeliveryDni($delivery->getDni());
            $delivery->setDeliveryAddressInfo($delivery->getAddressInfo());
            $delivery->setDeliveryPhone($delivery->getPhone());
            $delivery->setDeliveryPhone2($delivery->getPhone2());
            $delivery->setDeliveryPreferredSchedule($delivery->getPreferredSchedule());
        } else if ('existing' === $params['selectDelivery']) {
            /** @var Address $address */
            $address = $this->manager->getRepository('EcommerceBundle:Address')->find($params['existingDeliveryAddress']);

            $delivery->setDeliveryContactPerson($address->getContactPerson());
            $delivery->setDeliveryDni($address->getDni());
            $delivery->setDeliveryAddressInfo($address->getAddressInfo());
            $delivery->setDeliveryPhone($address->getPhone());
            $delivery->setDeliveryPhone2($address->getPhone2());
            $delivery->setDeliveryPreferredSchedule($address->getPreferredSchedule());
            
        } else if ('new' === $params['selectDelivery']) {
            $this->addUserDeliveryAddress($delivery);
        }

        $deliveryCountry = $this->manager->getRepository('CoreBundle:Country')->find('es');
        $delivery->setDeliveryCountry($deliveryCountry);

         
        $total = 0;
        $productPurchases = $this->manager->getRepository('EcommerceBundle:ProductPurchase')->findByTransaction($delivery->getTransaction());
        foreach ($productPurchases as $item) {
            if($item->getDeliveryExpenses()>0)
            $total = $total + $item->getDeliveryExpenses();
        }
        $delivery->setExpenses($total);
        if($total>0) $delivery->setExpensesType('store_pickup');
        else $delivery->setExpensesType('send');

        
        $this->saveUserBillingAddress($delivery);
        
      
              
        $this->manager->persist($delivery);
        $this->manager->flush();

        $this->session->set('delivery-id', $delivery->getId());
        $this->session->set('select-delivery', $params['selectDelivery']);
        if ('existing' === $params['selectDelivery']) {
            $this->session->set('existing-delivery-address', intval($params['existingDeliveryAddress']));
        } else {
            $this->session->remove('existing-delivery-address');
        }

        $this->session->save();
    }
    
    /**
     * Save user billing address
     *
     * @param Delivery $delivery
     */
    private function saveUserBillingAddress($delivery)
    {
                    
        
        // get billing address
        /** @var Address $billingAddress */
        $billingAddress = $this->manager->getRepository('EcommerceBundle:Address')->findOneBy(array(
            'actor' => $this->securityContext->getToken()->getUser(),
            'forBilling' => true
        ));

        
            
        // build new billing address when it does not exist
        if (is_null($billingAddress)) {
            $billingAddress = new Address();
            $billingAddress->setForBilling(true);
            $billingAddress->setActor($this->securityContext->getToken()->getUser());
        }

        
            
        $billingAddress->setContactPerson($delivery->getContactPerson());
        $billingAddress->setDni($delivery->getDni());
        $billingAddress->setAddressInfo($delivery->getAddressInfo());
        $billingAddress->setPhone($delivery->getPhone());
        $billingAddress->setPhone2($delivery->getPhone2());
        $billingAddress->setPreferredSchedule($delivery->getPreferredSchedule());
        $country = $this->manager->getRepository('CoreBundle:Country')->find('es');
        $billingAddress->setCountry($country);

        $this->manager->persist($billingAddress);
        $this->manager->flush();
      
    }
    
    /**
     * Check if a delivery ID is saved in session
     *
     * @return bool
     */
    public function isDeliverySaved()
    {
        return $this->session->has('delivery-id');
    }
    
    public function getRedirectUrlInvoice($delivery=null)
    {
        $invoice = $this->createInvoice($delivery);
        $this->cleanSession();

        $this->session->getFlashBag()->add(
            'success',
            'transaction.success'
        );

        return $this->router->generate('front_profile_showinvoice', array('number' => $invoice->getInvoiceNumber()));
    }
    
     /**
     * Create invoice from order
     *
     * @param Delivery $delivery
     *
     * @return Invoice
     */
    public function createInvoice($delivery=null, $transaction=null)
    {
        /** @var Transaction $transaction */
        if(is_null($transaction))
        $transaction = $this->getCurrentTransaction();
        
        
        $invoiceNumber = $this->manager->getRepository('EcommerceBundle:Invoice')->getNextNumber();

        $invoice = new Invoice();
        $invoice->setInvoiceNumber($invoiceNumber);
        //Actor invoices  = ONE SHOT
        
        if($transaction->getItems()->first()->getAdvert() instanceof Advert || 
           $transaction->getItems()->first()->getProduct() instanceof Product
            ){
            $invoice->setFullName($this->securityContext->getToken()->getUser()->getFullName());
            if(!is_null($delivery)){
               $invoice->setDni($delivery->getDni());
                $invoice->setAddressInfo($delivery->getAddressInfo()); 
            }else{
                if(count($transaction->getActor()->getAddresses()) > 0){
                    $invoice->setAddressInfo($transaction->getActor()->getAddresses()->first());
                }
            }
            
            $invoice->setTransaction($transaction);
            $this->manager->persist($invoice);
            $this->manager->flush();

            $totals = $this->calculateTotals($transaction, $delivery);

           
            if($transaction->getItems()->first()->getProduct() instanceof Product){
                $this->mailer->sendPurchaseNotification($invoice);
                $this->mailer->sendPurchaseConfirmationMessage($invoice, $totals['amount']);
            }elseif($transaction->getItems()->first()->getAdvert() instanceof Advert){
                $this->mailer->sendAdvertPurchaseNotification($invoice);
                $this->mailer->sendAdvertPurchaseConfirmationMessage($invoice, $totals['amount']);
            }
            
        
        }
        elseif($transaction->getItems()->first()->getPlan() instanceof Plan){
            $name = $transaction->getActor()->getName();
            $invoice->setFullName($name);
            $invoice->setAddress($transaction->getActor()->getAddress());
            $invoice->setPostalCode($transaction->getActor()->getPostalCode());
            $invoice->setCity($transaction->getActor()->getCity());
            if(!is_null($transaction->getActor()->getState()))$invoice->setState($transaction->getActor()->getState());
            $invoice->setCountry($transaction->getActor()->getCountry());
            
            $invoice->setTransaction($transaction);
            $this->manager->persist($invoice);
            $this->manager->flush();

            $totals = $this->calculateTotals($transaction, $delivery);

            $this->mailer->sendPlanPurchaseNotification($invoice);
            $this->mailer->sendPlanPurchaseConfirmationMessage($invoice, $totals['amount']);

        }
       

        return $invoice;
    }

    /**
     * Clean checkout parameters from session and void the shopping cart
     */
    public function cleanSession()
    {
        // remove checkout session parameters
        $this->session->remove('select-delivery');
        $this->session->remove('delivery-id');
        $this->session->remove('existing-delivery-address');
        $this->session->remove('transaction-id');

        // abandon cart
        $this->cartProvider->abandonCart();
    }
    
    /**
     * Get billing address
     *
     * @return Address
     */
    public function getBillingAddress($securityContext)
    {
        $user = $securityContext->getToken()->getUser();
        if (!$user || !is_object($user)) {
            throw new \LogicException(
                'The getBillingAddress cannot be used without an authenticated user!'
            );
        }
        /** @var Address $address */
        $address = $this->manager->getRepository('EcommerceBundle:Address')
            ->findOneBy(array(
                    'actor'       => $user,
                    'forBilling' => true
                ));

        // if it does not exist, create a new one
        if (is_null($address)) {
            $address = new Address();
            $address->setForBilling(true);
            $country = $this->manager->getRepository('CoreBundle:Country')->find('es');
            $address->setCountry($country);
            $address->setActor($this->securityContext->getToken()->getUser());
        }

        return $address;
    }
    
    /**
     * Check if current user is the transaction owner
     *
     * @param Transaction $transaction
     *
     * @return boolean
     */
    public function isCurrentUserOwner(Transaction $transaction)
    {
        if($this->securityContext->getToken()->getUser()->isGranted('ROLE_ADMIN')){
            return true;
        }
        
        $currentUserId = $this->securityContext->getToken()->getUser()->getId();
        //actor owner
        if($transaction->getActor() instanceof Actor){
            if($currentUserId ==  $transaction->getActor()->getId()){
                return true;
            }elseif($currentUserId == $transaction->getItems()->first()->getProduct()->getActor()->getId()){
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Return array of redsys data
     *
     * @param array $totals
     *
     * @return array
     */
    public function getRedsysData($totals)
    {
        if(in_array($this->environment, array('test', 'dev'))) {
            $redsys = $this->parameters['ecommerce']['redsys']['dev'];
        }else{
            $redsys = $this->parameters['ecommerce']['redsys']['prod'];
        }
        
        if( !isset($this->parameters['company']['name']) ||
            !isset($redsys['host']) ||
            !isset($redsys['code']) ||
            !isset($redsys['terminal']) ||
            !isset($redsys['secret']) ||
            !isset($redsys['bank_response_url']) ||
            !isset($redsys['transaction_type']) ||
            !isset($redsys['return_url']) || 
            !isset($redsys['cancel_url']) ||
            !isset($redsys['currency'])
            )
            throw new \Exception('All ecommerce paramters must be setted.');
        
        
         
        $order=date('ymdHis');
        $amount='25';
        $product='Hat';

        $message = $amount.$order.$redsys['code'].$redsys['currency'].$redsys['transaction_type'].$redsys['bank_response_url'].$redsys['secret'];
        $signature = strtoupper(sha1($message));
        
        return array(
            'url_tpvv' => $redsys['host'],
            'name' => $this->parameters['company']['name'],
            'code' => $redsys['code'],
            'terminal' => $redsys['terminal'],
            'order' => $order,
            'product' => $product,
            'amount' => $amount,
            'currency' => $redsys['currency'],
            'transactionType' => $redsys['transaction_type'],
            'urlMerchant' => $redsys['bank_response_url'],
            'urlOK' => $redsys['return_url'],
            'urlKO' => $redsys['cancel_url'],
            'signature' => $signature
            
        );
    }
    
    /**
     * Proccess sale transaction
     *
     * @param Transaction $transaction
     * @param Delivery $delivery
     *
     * @return stdClass
     */
    public function processPaypalSale(Transaction $transaction, $delivery=null, $credtCard=null)
    {
        
        $totals = $this->calculateTotals($transaction, $delivery);
  
        $this->paypalToken();
        
        if(is_null($credtCard)){
            $paymentMehod = array("payment_method" => "paypal");
        }else{
            $paymentMehod = array(
            "payment_method" => "credit_card",
            "funding_instruments" => array(
                array(
                  "credit_card" => $credtCard
                )
              )
            );
        }
        
        
        $returnValues = array();
        foreach ($transaction->getItems() as $productPurchase) {
            $sub = array();
            $sub['quantity'] = $productPurchase->getQuantity();
            if($productPurchase->getProduct() instanceof Product){
                $sub['name'] = $productPurchase->getProduct()->getName();
                $sub['price'] = number_format($productPurchase->getProduct()->getPrice(), 2);
            }elseif($productPurchase->getAdvert() instanceof Advert){
                $sub['name'] = $productPurchase->getAdvert()->getTitle();
                $sub['price'] = number_format($this->parameters['advert_unit_price'], 2);
            }
            
            
            $sub['sku'] = $productPurchase->getId();
            $sub['currency'] = "EUR";
            $returnValues[] = $sub;
        }
        
        $host = $this->parameters['ecommerce']['paypal']['host'];
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
                                'return_url' => $this->parameters['ecommerce']['paypal']['return_url'],
                                'cancel_url' => $this->parameters['ecommerce']['paypal']['cancel_url']
                        )
                    );

        $json = json_encode($payment);
        $json_resp = $this->paypalCall('POST', $url, $json);
                
        if(!is_null($credtCard)){
            if($json_resp['state'] == 'approved'){
                 //UPDATE TRANSACTION
                $transaction->setStatus(Transaction::STATUS_PAID);
                $transaction->setPaymentMethod(Transaction::PAYMENT_METHOD_CREDIT_CARD);
                $transaction->setPaymentDetails(json_encode($json_resp));
                $this->manager->persist($transaction);
                $this->manager->flush();
                
                //confirmation payment
                $answer = new \stdClass();
                $answer->redirectUrl = $this->router->generate('ecommerce_checkout_confirmationpayment');

                return $answer;
            }else{
                //cancel payment
                $answer = new \stdClass();
                $answer->redirectUrl = $this->parameters['ecommerce']['paypal']['cancel_url'];

                return $answer;
            }
        }else{
            //UPDATE TRANSACTION
            $transaction->setStatus(Transaction::STATUS_PENDING);
            $transaction->setPaymentMethod(Transaction::PAYMENT_METHOD_PAYPAL);
            $transaction->setPaymentDetails(json_encode($json_resp));
            $this->manager->persist($transaction);
            $this->manager->flush();


            foreach ($json_resp['links'] as $link) {
                    if($link['rel'] == 'execute'){
                            $payment_execute_url = $link['href'];
                            $payment_execute_method = $link['method'];
                    }elseif($link['rel'] == 'approval_url'){
                            $payment_approval_url = $link['href'];
                            $payment_approval_method = $link['method'];
                    }
            }

            $answer = new \stdClass();
            $answer->redirectUrl = $payment_approval_url;

            return $answer;
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
    public function processPaypalSaleAdvert(Transaction $transaction, $delivery=null, $credtCard=null)
    {
        
        $totals = $this->calculateTotalsAdvert($transaction, $delivery);
  
        $this->paypalToken();
        
        if(is_null($credtCard)){
            $paymentMehod = array("payment_method" => "paypal");
        }else{
            $paymentMehod = array(
            "payment_method" => "credit_card",
            "funding_instruments" => array(
                array(
                  "credit_card" => $credtCard
                )
              )
            );
        }
        
        
        $returnValues = array();
        foreach ($transaction->getItems() as $productPurchase) {
            $sub = array();
            $sub['quantity'] = $productPurchase->getQuantity();
            if($productPurchase->getProduct() instanceof Product){
                $sub['name'] = $productPurchase->getProduct()->getName();
                $sub['price'] = number_format($productPurchase->getProduct()->getPrice(), 2);
            }elseif($productPurchase->getAdvert() instanceof Advert){
                $sub['name'] = $productPurchase->getAdvert()->getTitle();
                $sub['price'] = number_format($totals['amount_clean'] / $productPurchase->getQuantity(), 2) ;
            }
            
            
            $sub['sku'] = $productPurchase->getId();
            $sub['currency'] = "EUR";
            $returnValues[] = $sub;
        }
        
        $host = $this->parameters['ecommerce']['paypal']['host'];
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
                                'return_url' => $this->parameters['ecommerce']['paypal']['return_url'],
                                'cancel_url' => $this->parameters['ecommerce']['paypal']['cancel_url']
                        )
                    );

        $json = json_encode($payment);
        $json_resp = $this->paypalCall('POST', $url, $json);
        
        
    
        if(!is_null($credtCard)){
            if(isset($json_resp['state']) && $json_resp['state'] == 'approved'){
                 //UPDATE TRANSACTION
                $transaction->setStatus(Transaction::STATUS_PAID);
                $transaction->setPaymentMethod(Transaction::PAYMENT_METHOD_CREDIT_CARD);
                $transaction->setPaymentDetails(json_encode($json_resp));
                $this->manager->persist($transaction);
                $this->manager->flush();
                
                //confirmation payment
                $answer = new \stdClass();
                $answer->redirectUrl = $this->router->generate('ecommerce_checkout_confirmationpayment');

                return $answer;
            }else{
                print_r($json_resp);die();
                //cancel payment
                $answer = new \stdClass();
                $answer->redirectUrl = $this->parameters['ecommerce']['paypal']['cancel_url'];

                return $answer;
            }
        }else{
            //UPDATE TRANSACTION
            $transaction->setStatus(Transaction::STATUS_PENDING);
            $transaction->setPaymentMethod(Transaction::PAYMENT_METHOD_PAYPAL);
            $transaction->setPaymentDetails(json_encode($json_resp));
            $this->manager->persist($transaction);
            $this->manager->flush();


            foreach ($json_resp['links'] as $link) {
                    if($link['rel'] == 'execute'){
                            $payment_execute_url = $link['href'];
                            $payment_execute_method = $link['method'];
                    }elseif($link['rel'] == 'approval_url'){
                            $payment_approval_url = $link['href'];
                            $payment_approval_method = $link['method'];
                    }
            }

            $answer = new \stdClass();
            $answer->redirectUrl = $payment_approval_url;

            return $answer;
        }
        
    }
    
    /**
     * Create PayPal token
     *
     */
    public function paypalToken($print=false){
        # Sandbox
        $host = $this->parameters['ecommerce']['paypal']['host'];
        $url = $host.'/v1/oauth2/token'; 
        $postdata = 'grant_type=client_credentials';
        
//        if($print)
//        print_r($this->parameters['ecommerce']['paypal']['client_id']. ":" . $this->parameters['ecommerce']['paypal']['secret']);echo PHP_EOL;
        $curl = curl_init($url); 
        curl_setopt($curl, CURLOPT_POST, true); 
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_USERPWD, $this->parameters['ecommerce']['paypal']['client_id']. ":" . $this->parameters['ecommerce']['paypal']['secret']);
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
    
    /**
     * Proccess plan
     *
     * @param Plan $plan
     *
     * @return stdClass
     */
    public function proccessPlan(Plan $plan){
        $this->createPaypalPlan($plan);
        $this->activePaypalPlan($plan);
    }
    
    public function createPaypalPlan(Plan $plan)
    {
       
        $this->paypalToken();
        
        $planConfig = array();
        $trial = false;
        $setupFee = array(
                    "currency"=> "EUR",
                    "value"=> $plan->getSetupAmount()
                );
        if($plan->getTrialCycles() > 0) {
            $trial = true;
            $planConfig[] = array(
                "name" => "Trial Plan",
                "type" => "TRIAL",
                "frequency_interval" =>  $plan->getTrialFrequencyInterval(),
                "frequency" => $plan->getTrialFrequency(),
                "cycles" => $plan->getTrialCycles(),
                "amount" => array(
                    "currency" => "EUR",
                    "value" => $plan->getTrialAmount()
                ),
                "charge_models" => array(array(
                        "type" => "TAX",
                        "amount" => array(
                            "currency" => "EUR",
                            "value" => "0"
                        )), array(
                        "type" => "SHIPPING",
                        "amount" => array(
                            "currency" => "EUR",
                            "value" => "0"
                        )))
            );
        }
        
         $planConfig[] = array(
                "name" => "Standard Plan",
                "type" => "REGULAR",
                "frequency_interval" =>  $plan->getFrequencyInterval(),
                "frequency" => $plan->getFrequency(),
                "cycles" => $plan->getCycles(),
                "amount" => array(
                    "currency" => "EUR",
                    "value" => $plan->getAmount()
                ),
                "charge_models" => array(array(
                        "type" => "TAX",
                        "amount" => array(
                            "currency" => "EUR",
                            "value" => "0"
                        )), array(
                        "type" => "SHIPPING",
                        "amount" => array(
                            "currency" => "EUR",
                            "value" => "0"
                        )))
            );
         
        
        $planPayPal = array(
                        "name" => $plan->getName(),
                        "description" => $plan->getDescription(),
                        "type" => "fixed",
                        "payment_definitions" => $planConfig,
                        "merchant_preferences" => array(
                                "setup_fee" => $setupFee,
                                "return_url" => $this->parameters['ecommerce']['paypal']['return_url'],
                                "cancel_url" => $this->parameters['ecommerce']['paypal']['cancel_url'],
                                "max_fail_attempts" => "0",
                                "auto_bill_amount" => "YES",
                                "initial_fail_amount_action" => "CONTINUE"
                            )
                        );
    
 
        $host = $this->parameters['ecommerce']['paypal']['host'];
        $url = $host.'/v1/payments/billing-plans';
      
        $json = json_encode($planPayPal);
        $answer = $this->paypalCall('POST', $url, $json);
        
        if(isset($answer['id']) && isset($answer['state']) && $answer['state'] == 'CREATED'){
            $plan->setPaypalId($answer['id']);
            $plan->setState($answer['state']);
            $this->manager->persist($plan);
            $this->manager->flush();
        }
        return $answer;
    }

    public function activePaypalPlan(Plan $plan)
    {
        
        $this->paypalToken();
      
 
        $data ='[
                {
                    "op": "replace",
                    "path": "/",
                    "value": {
                        "state": "ACTIVE"
                    }
                }
            ]';

 
        $host = $this->parameters['ecommerce']['paypal']['host'];
        $url = $host.'/v1/payments/billing-plans/'.$plan->getPaypalId();
        $answer = $this->paypalCall('PATCH', $url, $data);
        
        $payPalPlan = $this->getPaypalPlan($plan);
         
        
        if(isset($payPalPlan['state']) && $payPalPlan['state'] == 'ACTIVE'){
            $plan->setState($payPalPlan['state']);
            $plan->setActive(true);
            $this->manager->flush();
        }
        
        return $payPalPlan;
    }
    
    public function getPaypalPlan(Plan $plan)
    {
        
        $this->paypalToken();
      
        $host = $this->parameters['ecommerce']['paypal']['host'];
        $url = $host.'/v1/payments/billing-plans/'.$plan->getPaypalId();
        
        $answer = $this->paypalCall('GET', $url);
                
        return $answer;
    }
    
    
    public function createPaypalAgreement(Agreement $agreement, $credtCard=null)
    {
        $this->paypalToken();
      
        $paymentMehod = array();
        if($agreement->getPaymentMethod() == 'paypal') {
            $paymentMehod = array("payment_method" => "paypal");
        }elseif($agreement->getPaymentMethod() == 'credit_card') {
            if(is_null($credtCard)) throw new \Exception('credit card values must be send');
            $paymentMehod = array(
            "payment_method" => "credit_card",
            "funding_instruments" => array(
                array(
                  "credit_card" => $credtCard
                )
              )
            );
        }
        $agreementPayPal = array(
                "name" => $agreement->getName(),
                "description" => $agreement->getDescription(),
                "start_date" => date('Y-m-d')."T".date('H:i:s')."Z",
                "plan"=> array(
                    "id" => $agreement->getPlan()->getPaypalId()
                ),
                "payer" => $paymentMehod
            );
       
        $host = $this->parameters['ecommerce']['paypal']['host'];
        $url = $host.'/v1/payments/billing-agreements';
        $json = json_encode($agreementPayPal);
        $answer = $this->paypalCall('POST', $url, $json);

        /**
         * The payment are efective at final of cycle of service
         * so this "status" is just for agreement status
         * any transaction must be created except setup_fee setted in the plan
         **/
        
        //if some error in response
        if(isset($answer['status']) && $answer['status'] == 'error'){
            $agreement->setStatus($answer['status'].':'.  json_encode($answer));
        }elseif($agreement->getPaymentMethod() == 'paypal'){
            if($this->hasApprovalLink($answer)){
                //STATUS_PENDING_APPROVAL
                $agreement->setStatus($answer['state']);
            }else{
                //put status pending if paypal payment methods to redirect to approve
                $agreement->setStatus($answer['state'].'-pending');
            }
        }elseif($agreement->getPaymentMethod() == 'credit_card'){
            if($answer['state'] == 'Active'){
                 //we need to move this worlflow transaction
                $agreement->setStatus($answer['state']);
                $agreement->setPaypalId($answer['id']);
                $agreement->setOutstandingAmount($answer['agreement_details']['outstanding_balance']['value']);
                $agreement->setCyclesRemaining($answer['agreement_details']['cycles_remaining']);
                $agreement->setNextBillingDate($answer['agreement_details']['next_billing_date']);
                $agreement->setFinalPaymentDate($answer['agreement_details']['final_payment_date']);
                $agreement->setFailedPaymentCount($answer['agreement_details']['failed_payment_count']);
            }
        }
        $this->manager->flush();
        $this->session->set('agreement', json_encode($answer));
        $this->session->save();
      
        //If setupAmount is diferent than 0
        //get first completed transaction agreement
        //and create transaction
        if($agreement->getPlan()->getSetupAmount() != 0){
            $transactions = $this->searchPaypalAgreementTransactions($agreement);
               foreach ($transactions['agreement_transaction_list'] as $transaction) {
                //Created: this is the first paypal transacttion
                //Expired: this is the last paypal transacttion
                //Failed: when transaction not billing
                if($transaction['status'] == 'Completed'){
                    $transaction = $this->createSale(
                            $agreement, 
                            $transaction['amount']['value'], 
                            $transaction['fee_amount']['value'],
                            $transaction['net_amount']['value'],
                            json_encode($transaction)
                            );
                }
            }
        }
        
        
        return $answer;
    }
 
    public function hasApprovalLink($answer) {
        if(isset($answer['links'])) return true;
        return false;
    }
    
    public function createSale($agreement, $amount, $feeAmount, $netAmount, $details) 
    {
        
        $transaction = new Transaction();
        $transaction->setTransactionKey(uniqid());
        $transaction->setStatus(Transaction::STATUS_PAID);
        $transaction->setTotalPrice($netAmount);
        $transaction->setTax(abs($feeAmount));
        $transaction->setPaymentMethod($agreement->getPaymentMethod());
        $transaction->setPaymentDetails($details);
        $transaction->setActor($agreement->getContract()->getActor());
        $agreement->addTransaction($transaction);

        $productPurchase = new ProductPurchase();
        $productPurchase->setPlan($agreement->getPlan());//this relation exist in productPurchase-transaction-agreement-plan
        $productPurchase->setQuantity(1);
        $productPurchase->setBasePrice($amount);
        $productPurchase->setTotalPrice($amount);
        $productPurchase->setTransaction($transaction);
        $productPurchase->setCreated(new \DateTime('now'));
        $productPurchase->setReturned(false);
        $this->manager->persist($productPurchase);
        $transaction->addItem($productPurchase);
        $this->manager->persist($transaction);
        $this->manager->flush();
        
        $this->createInvoice(null, $transaction);
        
    }
    
    public function getPaypalAgreement($id) {
        
        $this->paypalToken();
      
        $host = $this->parameters['ecommerce']['paypal']['host'];
        $url = $host.'/v1/payments/billing-agreements/'.$id;
        
        $answer = $this->paypalCall('GET', $url);
                
        return $answer;
                
    }
    public function cancelPaypalAgreement($agreement) {
         
        $this->paypalToken();
      
        $host = $this->parameters['ecommerce']['paypal']['host'];
        $url = $host.'/v1/payments/billing-agreements/'.$agreement->getPaypalId().'/cancel';
        
        $answer = $this->paypalCall('POST', $url, '{ "note" : "Cancel the agreement."}');
                
        return $answer;
    }
    
    public function suspendPaypalAgreement($agreement) {
         
        $this->paypalToken();
       
        $host = $this->parameters['ecommerce']['paypal']['host'];
        $url = $host.'/v1/payments/billing-agreements/'.$agreement->getPaypalId().'/suspend';
        
        $answer = $this->paypalCall('POST', $url, '{ "note" : "Suspending the agreement."}');
                
        return $answer;
    }
    
    public function reactivePaypalAgreement($agreement) {
         
        $this->paypalToken();
      
        $host = $this->parameters['ecommerce']['paypal']['host'];
        $url = $host.'/v1/payments/billing-agreements/'.$agreement->getPaypalId().'/re-activate';
        
        $answer = $this->paypalCall('POST', $url, '{ "note" : "Reactivating the agreement."}');;
                
        return $answer;
    }
    
    public function setOutstandingPaypalAgreement($agreement, $amount) {
         
        $this->paypalToken();
       
        $host = $this->parameters['ecommerce']['paypal']['host'];
        $url = $host.'/v1/payments/billing-agreements/'.$agreement->getPaypalId().'/set-balance';
        
        $answer = $this->paypalCall('POST', $url, '{ "value": "'.$amount.'", "currency": "EUR"}');

        return $answer;
    }
    
    public function billOutstandingPaypalAgreement($agreement, $amount) {
         
        $this->paypalToken();
       
        $host = $this->parameters['ecommerce']['paypal']['host'];
        $url = $host.'/v1/payments/billing-agreements/'.$agreement->getPaypalId().'/bill-balance';
        
        $answer = $this->paypalCall('POST', $url, '{"note": "Billing Balance Amount '.$amount.'€", "amount": {"value": "'.$amount.'", "currency": "EUR"} }');
               
        return $answer;
    }
    
    public function searchPaypalAgreementTransactions(Agreement $agreement) {
         
        $this->paypalToken();
      
        $startDate = $agreement->getCreated();
        $startDate->modify('-1 day');
        $endDate =  new DateTime('now');
        $endDate->modify('+1 day');
        $host = $this->parameters['ecommerce']['paypal']['host'];
        $url = $host.'/v1/payments/billing-agreements/'.$agreement->getPaypalId().'/transactions?start_date='.$startDate->format('Y-m-d').'&end_date='.$endDate->format('Y-m-d');
        $answer = $this->paypalCall('GET', $url);
                
        return $answer;
    }
    
    public function paypalCall($method, $url, $postdata=null) 
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
    
    public function createBillingPlanPayPal(){
        
        //Use this call to create a billing plan. The request sample shows the plan being created with a regular billing period.
        $call  = "curl -v POST https://api.sandbox.paypal.com/v1/payments/billing-plans \
        -H 'Content-Type:application/json' \
        -H 'Authorization: Bearer <Access-Token>' \ ";
        $call .= '-d \'{
            "name": "T-Shirt of the Month Club Plan",
            "description": "Template creation.",
            "type": "fixed",
            "payment_definitions": [
                {
                    "name": "Regular Payments",
                    "type": "REGULAR",
                    "frequency": "MONTH",
                    "frequency_interval": "2",
                    "amount": {
                        "value": "100",
                        "currency": "USD"
                    },
                    "cycles": "12",
                    "charge_models": [
                        {
                            "type": "SHIPPING",
                            "amount": {
                                "value": "10",
                                "currency": "USD"
                            }
                        },
                        {
                            "type": "TAX",
                            "amount": {
                                "value": "12",
                                "currency": "USD"
                            }
                        }
                    ]
                }
            ],
            "merchant_preferences": {
                "setup_fee": {
                    "value": "1",
                    "currency": "USD"
                },
                "return_url": "http://www.return.com",
                "cancel_url": "http://www.cancel.com",
                "auto_bill_amount": "YES",
                "initial_fail_amount_action": "CONTINUE",
                "max_fail_attempts": "0"
            }
        }\' ';
        
        
        
        $response = '{
            "id": "P-94458432VR012762KRWBZEUA",
            "state": "CREATED",
            "name": "T-Shirt of the Month Club Plan",
            "description": "Template creation.",
            "type": "FIXED",
            "payment_definitions": [   <==== https://developer.paypal.com/docs/api/#paymentdefinition-object
              {
                "id": "PD-50606817NF8063316RWBZEUA",
                "name": "Regular Payments",
                "type": "REGULAR",     <===== type:string Type of the payment definition. Allowed values: TRIAL, REGULAR. Required.
                "frequency": "Month",
                "amount": {
                  "currency": "USD",
                  "value": "100"
                },
                "charge_models": [
                  {
                    "id": "CHM-55M5618301871492MRWBZEUA",
                    "type": "SHIPPING",
                    "amount": {
                      "currency": "USD",
                      "value": "10"
                    }
                  },
                  {
                    "id": "CHM-92S85978TN737850VRWBZEUA",
                    "type": "TAX",
                    "amount": {
                      "currency": "USD",
                      "value": "12"
                    }
                  }
                ],
                "cycles": "12",
                "frequency_interval": "2"
              }
            ],
            "merchant_preferences": {
              "setup_fee": {
                "currency": "USD",
                "value": "1"
              },
              "max_fail_attempts": "0",
              "return_url": "http://www.return.com",
              "cancel_url": "http://www.cancel.com",
              "auto_bill_amount": "YES",
              "initial_fail_amount_action": "CONTINUE"
            },
            "create_time": "2014-07-31T17:41:55.920Z",
            "update_time": "2014-07-31T17:41:55.920Z",
            "links": [
              {
                "href": "https://api.sandbox.paypal.com/v1/payments/billing-plans/P-94458432VR012762KRWBZEUA",
                "rel": "self",
                "method": "GET"
              }
            ]
          }';
        
        //You can update the information for an existing billing plan. The state of a plan must be active before a billing agreement is created.
        $activePlan = 'curl -v -k -X PATCH \'https://api.sandbox.paypal.com/v1/payments/billing-plans/P-94458432VR012762KRWBZEUA\' \
                -H "Content-Type: application/json" \
                -H "Authorization: Bearer <Access-Token>" \
                -d \'[
                    {
                        "path": "/",
                        "value": {
                            "state": "ACTIVE"
                        },
                        "op": "replace"
                    }
                ]\' ';
        //Returns the HTTP status of 200 if the call is successful.
        
        
        
        //Use this call to get details about a specific billing plan.
        $billingPlan = "curl -v -X GET https://api.sandbox.paypal.com/v1/payments/billing-plans/P-94458432VR012762KRWBZEUA \
                            -H 'Content-Type:application/json' \
                            -H 'Authorization: Bearer <Access-Token>'";
        
        //token   =   curl https://api.sandbox.paypal.com/v1/oauth2/token  -H "Accept: application/json"  -H "Accept-Language: en_US"  -u "AUBVQzQgncps4IjuYku4Uy0u5Ocx67I_ywC96QJjIbiq4F9G9smzdcG7p-7zekMtIxl10I_3COkW1cMw:EOsCDBsxxur44b8InlYuBB68goyevR49bhlrQD93_0gi7p_0urbZrMJSEbpxLVj7j46aaQawVClWg1up"  -d "grant_type=client_credentials"

            
        $billingPlanList = "curl -v -X GET https://api.sandbox.paypal.com/v1/payments/billing-plans?page_size=3&status=CREATED&page=2&total_required=yes \
                                -H 'Content-Type:application/json' \
                                -H 'Authorization: Bearer A101.h3kb0G0goI-S710SUc0A45sYSgGZiPw4JNSaB-VD6ci01xZh_asI248oRUPfo6DM.UDWue_jEs6wBcLd_ivGlwjmsfti' \
                                -d '{}'";
    }
    
    public function createBillingPlanAgreementPayPal(){
        //create billing agreement for a plan
        $agreement = 'curl -v POST https://api.sandbox.paypal.com/v1/payments/billing-agreements \
                        -H \'Content-Type:application/json\' \
                        -H \'Authorization: Bearer <Access-Token>\' \
                        -d \'{
                            "name": "T-Shirt of the Month Club Agreement",
                            "description": "Agreement for T-Shirt of the Month Club Plan",
                            "start_date": "2015-02-19T00:37:04Z",
                            "plan": {
                                "id": "P-94458432VR012762KRWBZEUA"
                            },
                            "payer": {
                                "payment_method": "credit_card",
                                "funding_instruments": [
                                  {
                                    "credit_card": {
                                      "number": "5500005555555559",
                                      "type": "mastercard",
                                      "expire_month": 12,
                                      "expire_year": 2018,
                                      "cvv2": 111,
                                      "first_name": "Betsy",
                                      "last_name": "Buyer"
                                    }
                                  }
                                ]
                              },
                            "shipping_address": {
                                "line1": "111 First Street",
                                "city": "Saratoga",
                                "state": "CA",
                                "postal_code": "95070",
                                "country_code": "US"
                            }
                        }\' ';
        //After successfully creating the agreement, direct the user to the approval_url on the PayPal site so that the user can approve the agreement.
        //execute link
        
        $responseAgreement  = '{
                    "name": "T-Shirt of the Month Club Agreement",
                    "description": "Agreement for T-Shirt of the Month Club Plan",
                    "plan": {
                      "id": "P-94458432VR012762KRWBZEUA",
                      "state": "ACTIVE",
                      "name": "T-Shirt of the Month Club Plan",
                      "description": "Template creation.",
                      "type": "FIXED",
                      "payment_definitions": [
                        {
                          "id": "PD-50606817NF8063316RWBZEUA",
                          "name": "Regular Payments",
                          "type": "REGULAR",
                          "frequency": "Month",
                          "amount": {
                            "currency": "USD",
                            "value": "100"
                          },
                          "charge_models": [
                            {
                              "id": "CHM-92S85978TN737850VRWBZEUA",
                              "type": "TAX",
                              "amount": {
                                "currency": "USD",
                                "value": "12"
                              }
                            },
                            {
                              "id": "CHM-55M5618301871492MRWBZEUA",
                              "type": "SHIPPING",
                              "amount": {
                                "currency": "USD",
                                "value": "10"
                              }
                            }
                          ],
                          "cycles": "12",
                          "frequency_interval": "2"
                        }
                      ],
                      "merchant_preferences": {
                        "setup_fee": {
                          "currency": "USD",
                          "value": "1"
                        },
                        "max_fail_attempts": "0",
                        "return_url": "http://www.return.com",
                        "cancel_url": "http://www.cancel.com",
                        "auto_bill_amount": "YES",
                        "initial_fail_amount_action": "CONTINUE"
                      }
                    },
                    "links": [
                      {
                        "href": "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-0JP008296V451950C",
                        "rel": "approval_url",
                        "method": "REDIRECT"
                      },
                      {
                        "href": "https://api.sandbox.paypal.com/v1/payments/billing-agreements/EC-0JP008296V451950C/agreement-execute",
                        "rel": "execute",
                        "method": "POST"
                      }
                    ],
                    "start_date": "2015-02-19T00:37:04Z"
                  }'; 
        
        //Use this call to execute an agreement after the buyer approves it.
        $executeAgreement = "curl -v POST https://api.sandbox.paypal.com/v1/payments/billing-agreements/EC-0JP008296V451950C/agreement-execute \
                                -H 'Content-Type:application/json' \
                                -H 'Authorization: Bearer <Access-Token>' \
                                -d '{}'";
        $responseExecute = '{
                                "id": "I-0LN988D3JACS",
                                "links": [
                                  {
                                    "href": "https://api.sandbox.paypal.com/v1/payments/billing-agreements/I-0LN988D3JACS",
                                    "rel": "self",
                                    "method": "GET"
                                  }
                                ]
                              }';
        
        //Use this call to suspend an agreement.
        $suspendAgreement = "curl -v POST https://api.sandbox.paypal.com/v1/payments/billing-agreements/I-0LN988D3JACS/suspend \
                            -H 'Content-Type:application/json' \
                            -H 'Authorization: Bearer <Access-Token>' \
                            -d '{
                                \"note\": \"Suspending the agreement.\"
                            }' ";
        //Response : Returns the HTTP status of 204 if the call is successful.
        
        //Use this call to reactivate an agreement.
        $reactiveAgreement = "curl -v POST https://api.sandbox.paypal.com/v1/payments/billing-agreements/I-0LN988D3JACS/re-activate \
                                    -H 'Content-Type:application/json' \
                                    -H 'Authorization: Bearer <Access-Token>' \
                                    -d '{
                                        \"note\": \"Reactivating the agreement.\"
                                    }' ";
        //Response : Returns the HTTP status of 204 if the call is successful.
        
        //Use this call to cancel an agreement.
        $cancelAgreement = "curl -v POST https://api.sandbox.paypal.com/v1/payments/billing-agreements/I-0LN988D3JACS/cancel \
                                -H 'Content-Type:application/json' \
                                -H 'Authorization: Bearer <Access-Token>' \
                                -d '{
                                    \"note\": \"Canceling the agreement.\"
                                }' ";
        
        //Response : Returns the HTTP status of 204 if the call is successful.
        
        //Use this call to search for the transactions within a billing agreement.
        $transactionByAgreenment = "curl -v GET https://api.sandbox.paypal.com/v1/payments/billing-agreements/I-0LN988D3JACS/transactions?start_date=yyyy-mm-dd&end_date=yyyy-mm-dd \
                                        -H 'Content-Type:application/json' \
                                        -H 'Authorization: Bearer <Access-Token>'";
        
        $responseTransactionByAgreenment = '{
                                "agreement_transaction_list": [
                                  {
                                    "transaction_id": "I-0LN988D3JACS",
                                    "status": "Created",
                                    "transaction_type": "Recurring Payment",
                                    "payer_email": "bbuyer@example.com",
                                    "payer_name": "Betsy Buyer",
                                    "time_stamp": "2014-06-09T09:29:36Z",
                                    "time_zone": "GMT"
                                  },
                                  {
                                    "transaction_id": "928415314Y5640008",
                                    "status": "Completed",
                                    "transaction_type": "Recurring Payment",
                                    "amount": {
                                      "currency": "USD",
                                      "value": "1.00"
                                    },
                                    "fee_amount": {
                                      "currency": "USD",
                                      "value": "-0.33"
                                    },
                                    "net_amount": {
                                      "currency": "USD",
                                      "value": "0.67"
                                    },
                                    "payer_email": "bbuyer@example.com",
                                    "payer_name": "Betsy Buyer",
                                    "time_stamp": "2014-06-09T09:42:47Z",
                                    "time_zone": "GMT"
                                  },
                                  {
                                    "transaction_id": "I-0LN988D3JACS",
                                    "status": "Suspended",
                                    "transaction_type": "Recurring Payment",
                                    "payer_email": "bbuyer@example.com",
                                    "payer_name": "Betsy Buyer",
                                    "time_stamp": "2014-06-09T11:18:34Z",
                                    "time_zone": "GMT"
                                  },
                                  {
                                    "transaction_id": "I-0LN988D3JACS",
                                    "status": "Reactivated",
                                    "transaction_type": "Recurring Payment",
                                    "payer_email": "bbuyer@example.com",
                                    "payer_name": "Betsy Buyer",
                                    "time_stamp": "2014-06-09T11:18:48Z",
                                    "time_zone": "GMT"
                                  }
                                ]
                              }';
        
        //Use this call to set the outstanding amount of an agreement.
        $outstandingAgreement =  "curl -v POST https://api.sandbox.paypal.com/v1/payments/billing-agreements/I-0LN988D3JACS/set-balance \
                            -H 'Content-Type:application/json' \
                            -H 'Authorization: Bearer <Access-Token>' \
                            -d '{
                                \"value\": \"100\",
                                \"currency\": \"USD\"
                            }'";
        //Response : Returns the HTTP status of 204 if the call is successful.
        
        //Use this call to bill the outstanding amount of an agreement.
        $billOutstandingAgreement = "curl -v POST https://api.sandbox.paypal.com/v1/payments/billing-agreements/I-0LN988D3JACS/bill-balance \
                                        -H 'Content-Type:application/json' \
                                        -H 'Authorization: Bearer <Access-Token>' \
                                        -d '{
                                            \"note\": \"Billing Balance Amount\",
                                            \"amount\": {
                                                \"value\": \"100\",
                                                \"currency\": \"USD\"
                                            }
                                        }'";
        //Response : Returns the HTTP status of 204 if the call is successful.
    }
    
    //Billing Plans Errors
//    The following is a list of errors related to Billing Plans. We provide corrective action where available.
//
//    INTERNAL_SERVICE_ERROR
//    An internal service error has occurred
//    Resend the request at another time. If this error continues, contact PayPal Merchant Technical Support.
//
//    VALIDATION_ERROR
//    Invalid request - see details
//    There was a validation issue with your request - see details
//
//    REQUIRED_SCOPE_MISSING
//    Access token does not have required scope.
//    Obtain user consent using the correct scope required for this type of request.
//
//    UNAUTHORIZED_ACCESS
//    You don’t have permission to access this resource.
//    Please pass a valid plan id.
//
//    =======================================
//
//    Billing Agreements Errors
//    The following is a list of errors related to Billing Agreements. We provide corrective action where available.
//
//    INTERNAL_SERVICE_ERROR
//    An internal service error has occurred
//    Resend the request at another time. If this error continues, contact PayPal Merchant Technical Support.
//
//    SUBSCRIPTION_UNMAPPED_ERROR
//    Some unmapped business/ internal error has occurred
//    Some unmapped business/ internal error has occurred. Please look into message details for resolution.
//
//    ADDRESS_INVALID
//    Provided user address is invalid
//    Provided user address is invalid.
//
//    DUPLICATE_REQUEST_ID
//    The value of PayPal-Request-Id header has already been used
//    Resend the request using a unique PayPal-Request-Id.
//
//    VALIDATION_ERROR
//    Invalid request - see details
//    There was a validation issue with your request - see details
//
//    REQUIRED_SCOPE_MISSING
//    Access token does not have required scope.
//    Obtain user consent using the correct scope required for this type of request.
//
//    UNAUTHORIZED_AGREEMENT_REQUEST
//    You don’t have permission to create such agreement.
//    Obtain the permission to create agreement in one shot.
//
//    MERCHANT_COUNTRY_NOT_SUPPORTED
//    The merchant’s country is currently not supported
//    Merchant country not supported.
//
//    FEATURE_NOT_AVAILABLE
//    Recurring payments feature is not currently available; try again later
//    Recurring payments feature is not currently available; try again later.
//
//    INVALID_ARGS
//    Invalid argument; description field or custom field is empty and the status is active
//    Pass correct arguments in description field and make sure status is active.
//
//    WALLET_TOO_MANY_ATTEMPTS
//    You have exceeded the maximum number of payment attempts for this token.
//    Please create a new token and create agreement using that.
//
//    USR_BILLING_AGRMNT_NOT_ACTIVE
//    This transaction cannot be processed due to an invalid merchant configuration.
//    Occurs when the billing agreement is disabled or inactive.
//
//    ACCOUNT_RESTRICTED
//    This transaction cannot be processed. Please contact PayPal Customer Service.
//    Transaction cannot be processed. Please contact PayPal Customer Service.
//
//    INVALID_CC_NUMBER
//    This transaction cannot be processed. Please enter a valid credit card number and type.
//    Please enter a valid credit card number and type.
//
//    FEATURE_DISABLED
//    This transaction cannot be processed.
//    This feature is disabled for now.
//
//    CC_TYPE_NOT_SUPPORTED
//    The credit card type is not supported
//    Please use another type of credit card.
//
//    SHP_INVALID_COUNTRY_CODE
//    This transaction cannot be processed. Please enter a valid country code in the shipping address.
//    Please enter a valid country code in the shipping address.
//
//    MISSING_CVV2
//    This transaction cannot be processed without a Credit Card Verification number
//    Please enter cvv2 for credit-card.
//
//    INVALID_CURRENCY
//    This transaction cannot be processed due to an unsupported currency.
//    This currency is not supported right now.
//
//    BUSADD_STATE_UNSUPPORTED
//    This transaction cannot be processed. The country listed for your business address is not currently supported.
//    The country listed for your business address is not currently supported.
//
//    INVALID_TOKEN
//    The token is missing or is invalid
//    Please use a valid token.
//
//    INVALID_AMOUNT
//    Bill amount must be greater than 0
//    Please pass valid amount.
//
//    INVALID_PROFILE_STATUS
//    The profile status must be one of (A)ctive, (C)ancelled, or e(X)pired
//    Please enter valid profile status.
//
//    PAYER_ACCOUNT_DENIED
//    Payer’s account is denied
//    Payer’s account is denied.
//
//    PAYER_COUNTRY_NOT_SUPPORTED
//    The payer’s country is currently not supported
//    The payer’s country is currently not supported.
//
//    MERCHANT_ACCOUNT_DENIED
//    Merchant account is denied
//    Merchant account is denied.
//
//    CANNOT_MIX_CURRENCIES
//    Invalid currency code, all currency codes much match
//    Please use same currency for all the amount objects.
//
//    START_DATE_INVALID_FORMAT
//    Subscription start date should be valid
//    Please pass a valid start date.
//
//    INVALID_PROFILE_ID
//    The profile ID is invalid
//    Please provide a valid agreement ID.
//
//    INVALID_PROFILE_ACTION
//    Invalid action value provided
//    Please provide a valid action value.
//
//    INVALID_STATUS_TO_CANCEL
//    Invalid profile status for suspend action; profile should be active
//    Agreement should be active before suspending it.
//
//    INVALID_STATUS_TO_SUSPEND
//    Invalid profile status for reactivate action; profile should be suspended
//    Agreement should be suspended to perform this action.
//
//    INVALID_STATUS_TO_REACTIVATE
//    The activation type is invalid.
//    Please pass a valid activate type.
//
//    BILL_AMOUNT_GREATER_THAN_OUTSTANDING_BALANCE
//    Bill amount is greater than outstanding balance
//    Bill amount should be less than outstanding balance.
//
//    OUTSTANDING_PAYMENT_ALREADY_SCHEDULED
//    Another outstanding payment is scheduled
//    Another outstanding payment is scheduled
//
//    RECURRING_PAYMENT_SCHEDULED_WITHIN_24HOURS
//    Recurring payment scheduled within 24 hours, so we are not processing the bill outstanding amoun
//    Recurring payment scheduled within 24 hours, so we are not processing the bill outstanding amount.
//
//    CALL_FAILED_PAYMENT
//    Payment is failing
//    Payment is failing.
//
//    CANNOT_FIND_PROFILE_DESC
//    Profile description is invalid.
//    Please provide a valid description of agreement.
//
//    DPRP_DISABLED
//    DPRP is disabled for this merchant.
//    Please enable dprp for creating such agreement.
//
//    GATEWAY_DECLINE_CVV2
//    This transaction cannot be processed. Please enter a valid Credit Card Verification Number.
//    Please use a valid credit card.
//
//    PROCESSOR_DECLINE_INVALID_CC_COUNTRY
//    This credit card was issued from an unsupported country.
//    This credit card was issued from an unsupported country.
//
//    SHIPPING_ADDRESS_NOT_IN_RESIDENCE_COUNTRY
//    This transaction cannot be processed. The shipping country is not allowed by the buyer’s country of residence.
//    The shipping country is not allowed by the buyer’s country of residence.
//
//    CANT_INCREASE_OUTSTANDING_AMOUNT
//    Cannot increase delinquent amount
//    Bill outstanding amount cannot be increased.
//
//    TIME_TO_UPDATE_CLOSE_TO_BILLING_DATE
//    The time of the update is too close to the billing date
//    The time of the update is too close to the billing date.
//
//    SET_BALANCE_INVALID_CURRENCY_CODE
//    Invalid currency for delinquent amount
//    Please pass valid currency in bill-balance call.
//
//    STATUS_INVALID
//    Invalid profile status for reactivate action; profile should be suspended
//    Please pass valid status for agreement state change.
//
//    INTERNAL_ERROR
//    Internal Error
//    Resend the request at another time. If this error continues, contact PayPal Merchant Technical Support.
//
//    CC_STATUS_INVALID
//    Profile is not active
//    Profile is not in active state.


        
        
    /**
     * Process a bank transfer request
     *
     * @param Transaction $transaction
     */
    public function processBankTransfer(Transaction $transaction)
    {
        $transaction->setStatus(Transaction::STATUS_PENDING_TRANSFER);
        $transaction->setPaymentMethod(Transaction::PAYMENT_METHOD_BANK_TRANSFER);

        $this->manager->persist($transaction);
        $this->manager->flush();
        
        return true;
    }

    /**
     * Process a redsys transaction
     *
     * @param int                   $ds_response
     * @param Transaction                 $transaction
     *
     * @return boolean
     */
    public function processRedsysTransaction($ds_response, Transaction $transaction)
    {
        if ($ds_response > 99) {
            return false;
        }

        $transaction->setStatus(Transaction::STATUS_PAID);
        $transaction->setPaymentMethod(Transaction::PAYMENT_METHOD_CREDIT_CARD);

        $this->manager->persist($transaction);
        $this->manager->flush();

        return true;
    }
    
    /**
     * @param CartItemInterface $item
     * @param Request           $request
     *
     * @throws ItemResolvingException
     * @return CartItemInterface|void
     */
    public function resolve(CartItem $item, Request $request)
    {
        
        $productId = $request->query->get('id');
        $itemForm = $request->request->get('ecommercebundle_cartitemtype');
        
        $productRepository = $this->manager->getRepository('EcommerceBundle:Product');
        if (!$productId || !$product = $productRepository->find($productId)) {
            // no product id given, or product not found
            throw new \Exception('Requested product was not found');
        }

        // assign the product and quantity to the item
        $item->setProduct($product);
        $item->setQuantity(intval($itemForm['quantity']));

        // calculate item price adding the special charge
        $price = $product->getPrice();
        if ($this->parameters['company']['special_percentage_charge'] > 0) {
            $price += $price * ($this->parameters['company']['special_percentage_charge'] / 100);
        }
        $item->setUnitPrice(intval($price));

        return $item;
    }
    
    public function getProductStats(Product $product, $start, $end) 
    {
        $stats = $this->manager->getRepository('EcommerceBundle:Product')
                ->getProductStats($product, $start, $end);
        
        return $stats;
    }
    
}