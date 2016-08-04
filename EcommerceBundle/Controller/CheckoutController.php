<?php

namespace EcommerceBundle\Controller;

use CoreBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use EcommerceBundle\Form\DeliveryType;
use CoreBundle\Form\Model\Registration;
use EcommerceBundle\Form\BankTransferType;
use EcommerceBundle\Form\PayPalType;
use EcommerceBundle\Entity\Transaction;
use EcommerceBundle\Form\RedsysType;
use EcommerceBundle\Lib\RedsysResponse;
use EcommerceBundle\Entity\CartItem;
use EcommerceBundle\Form\CartType;
use EcommerceBundle\Entity\CreditCardForm;
use EcommerceBundle\Form\CreditCardType;
use EcommerceBundle\Entity\Contract;
use EcommerceBundle\Entity\Agreement;
use CoreBundle\Entity\EmailToken;
use DateTime;

class CheckoutController extends BaseController
{

    /*********************************
     ************* CART **************
     *********************************/
    /**
     * @Route("/cart/")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $products = $em->getRepository('EcommerceBundle:Product')->findBy(array());
        
        return array('products' => $products);
    }

    
    /**
     * @Route("/cart/product/{slug}")
     * @Template()
     */
    public function productAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('EcommerceBundle:Product')->findOneBySlug(array('slug' => $slug));
        
        return array('product' => $product);
    }
    
    /**
     * Displays current cart summary page.
     * The parameters includes the form .
     * 
     * @param Request
     * @return Response
     * 
     * @Route("/cart/detail")
     * @Template("EcommerceBundle:Checkout/cart:detail.html.twig")   
     */
    public function detailAction(Request $request)
    {
       
        $cart = $this->getCurrentCart();
        $form = $this->createForm('EcommerceBundle\Form\CartType', $cart);

        return array(
            'cart' => $cart,
            'form' => $form->createView()
        );
    }
  
    /**
     * Adds item to cart.
     * It uses the resolver service so you can populate the new item instance
     * with proper values based on current request.
     *
     * It redirect to cart summary page by default.
     *
     * @param Request $request
     * @return Response
     * 
     * @Route("/cart/add")
     * @Template("EcommerceBundle:Front:detail.html.twig")
     */
    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $coreManager = $this->get('core_manager');
        $checkoutManager = $this->get('checkout_manager');
        $cart = $this->getCurrentCart();
        $emptyItem = new CartItem();
        
        try {
            $item = $checkoutManager->resolve($emptyItem, $request);
        } catch (\Exception $exception) {
            // Write flash message
            print_r($exception->getMessage());die();
            return $this->redirect($this->generateUrl('ecommerce_checkout_detail'));
        }

        $price = $item->getProduct()->getPrice();
        $item->setUnitPrice($price);
        $freeTransport = $item->getProduct()->isFreeTransport();
        $item->setFreeTransport($freeTransport);
        //add
        $cart->addItem($item);
        //refresh
        $cart->calculateTotal();
        $cart->setTotalItems($cart->countItems());
        //save
        $em->persist($cart);
        $em->flush();
       
        // Write flash message
        $referer = $coreManager->getRefererPath($request);
        return $this->redirect($this->generateUrl('ecommerce_checkout_detail').'?referer='.$referer);
    }
    
    /**
     * This action is used to submit the cart summary form.
     * If the form and updated cart are valid, it refreshes
     * the cart data and saves it using the operator.
     *
     * If there are any errors, it displays the cart detail page.
     *
     * @param Request $request
     * @return Response
     * 
     * @Route("/cart/save")
     * @Template("EcommerceBundle:Front:detail.html.twig")
     */
    public function saveAction(Request $request)
    {
        $cart = $this->getCurrentCart();
        $form = $this->createForm(new CartType(), $cart);

        if ($form->bind($request)->isValid()) {
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($cart);
            $em->flush();
            
            return $this->redirect($this->generateUrl('ecommerce_checkout_identification'));
            
//            $event = new CartEvent($cart);
//            $event->isFresh(true);
//
//            // Update models
//            $this->dispatchEvent(SyliusCartEvents::CART_SAVE_INITIALIZE, $event);
//
//            // Write flash message
//            $this->dispatchEvent(SyliusCartEvents::CART_SAVE_COMPLETED, new FlashEvent());
        }

        return array(
            'cart' => $cart,
            'form' => $form->createView()
        );
    }
    
    /**
     * Removes item from cart.
     * It takes an item id as an argument.
     *
     * If the item is found and the current user cart contains that item,
     * it will be removed and the cart - refreshed and saved.
     *
     * @param mixed $id
     * @return Response
     * 
     * @Route("/cart/remove/{id}")
     */
    public function removeAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $cart = $this->getCurrentCart();
        $repository = $em->getRepository('EcommerceBundle:CartItem');
        $item = $repository->find($id);

        if (!$item || false === $cart->hasItem($item)) {
            // Write flash message
            return $this->redirect($this->generateUrl('ecommerce_checkout_detail'));
        }

        // Update models
        $cart->removeItem($item);
        $cart->setTotalItems(count($cart->getItems()));
        $em->flush();
        // Write flash message

        return $this->redirect($this->generateUrl('ecommerce_checkout_detail'));
    }

    /**
     * Returns current cart.
     *
     * @return CartInterface
     */
    public function getCurrentCart()
    {
        return $this->get('cart_provider')->getCart();
    }
    
    
    /*********************************
     ***********CHECKOUT**************
     *********************************/
    /**
     * Step 1: identification
     *
     * @return array
     *
     * @Route("/identification")
     * @Method("GET")
     * @Template
     */
    public function identificationAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('ecommerce_checkout_deliveryinfo'));
        }

        $session = $request->getSession();
        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        
        $registration = new Registration();
        $form = $this->createForm('CoreBundle\Form\RegistrationType', $registration);
        
        return array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
                'form'          => $form->createView()
            );
    }

    /**
     * Step 2: delivery info
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/delivery-info")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function deliveryInfoAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect($this->generateUrl('index'));
        }
        
        /** @var CheckoutManager $checkoutManager */
        $checkoutManager = $this->get('checkout_manager');
        //ere create a transaction
        $checkoutManager->updateTransaction();
        
        $transaction = $checkoutManager->getCurrentTransaction();
        $delivery = $checkoutManager->getDelivery($transaction);
        $securityContext = $this->get('security.token_storage');
        $manager = $this->get('doctrine')->getManager();
        $session = $this->get('session');

        $form = $this->createForm(new DeliveryType($securityContext, $manager, $session), $delivery);

        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $cart = $this->getCurrentCart();
                $this->get('checkout_manager')->saveDelivery($delivery, $request->get('delivery'), $cart);

                $url = $this->container->get('router')->generate('ecommerce_checkout_summary');

                return new RedirectResponse($url);
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }
    
    /**
     * Step 3: summary
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Route("/summary")
     * @Method({"GET", "POST"})
     * @Template
     */
    public function summaryAction(Request $request)
    {
        /** @var CheckoutManager $checkoutManager */
        $checkoutManager = $this->get('checkout_manager');

        if (false === $checkoutManager->isDeliverySaved()) {
            return $this->redirect($this->generateUrl('ecommerce_checkout_deliveryinfo'));
        }

        $transaction = $checkoutManager->getCurrentTransaction();
        $delivery = $checkoutManager->getDelivery();
        
        $totals = $checkoutManager->calculateTotals($transaction, $delivery);

        //Forms
        $transferForm = $this->createForm(new BankTransferType());
        $paypalForm = $this->createForm(new PayPalType());
        $redsysForm = $this->createResysForm($transaction, $totals);
        $braintreeForm = $this->createForm('EcommerceBundle\Form\BraintreeType');
        //cc form
        $creditCardform = $this->createCreditCardForm();
        

        //TODO: Refactor calculate todal delivery expenses
        $totalForDelivery = 0;
        foreach ($transaction->getItems() as $productPurchase) {
            if(!$productPurchase->getProduct()->isFreeTransport()){
                $totalForDelivery += $productPurchase->getTotalPrice() * $productPurchase->getQuantity();
            }
        }

        $parameters = $this->container->getParameter('core');
        $deliveryCosts = round((($totalForDelivery * $parameters['ecommerce']['delivery_expenses_percentage']) / 100),2);

        $vat = round(((($transaction->getTotalPrice() + $deliveryCosts) * $transaction->getVat()) / 100),2);
        $total = $transaction->getTotalPrice() + $vat + $deliveryCosts;
 
   
        // process payment method form
        if ($request->isMethod('POST')) {

            if ($request->request->has('braintree')) {
                $checkoutManager->processBraintree($transaction, $delivery, $request);

                return $this->redirect($checkoutManager->getRedirectUrlInvoice($delivery));
            }else if ($request->request->has('bank_transfer')) {
                $checkoutManager->processBankTransfer($transaction);

                return $this->redirect($checkoutManager->getRedirectUrlInvoice($delivery));
            }elseif ($request->request->has('paypal')) {
                $answer = $checkoutManager->processPaypalSale($transaction, $delivery);

                return $this->redirect($answer->redirectUrl);
            }elseif ($request->request->has('redsys')) {
                $answer = $checkoutManager->processRedsysSale($transaction, $delivery);

                return $this->redirect($answer->redirectUrl);
            }elseif ($request->request->has('credit_card')) {
                
                $creditCardform->bind($request);
                if ($creditCardform->isValid()){
                    
                    
                    $answer = $checkoutManager->processPaypalSale($transaction, $delivery, array(
                        "number" => $creditCardform->getNormData()->cardNo,
                        "type" => $creditCardform->getNormData()->cardType,
                        "expire_month" =>  $creditCardform->getNormData()->expirationDate->format('m'),
                        "expire_year" =>  $creditCardform->getNormData()->expirationDate->format('Y'),
                        "cvv2" =>  $creditCardform->getNormData()->CVV,
                        "first_name" =>  $creditCardform->getNormData()->firstname,
                        "last_name" =>  $creditCardform->getNormData()->lastname
                   ));
                    
                    return $this->redirect($answer->redirectUrl);
                }else {
                    die('invalid');
                }
                
                
            }
            
            
        }

        $returnValues = $checkoutManager->getRedsysData($totals);
//        $returnValues2 = $checkoutManager->getPaypalData($totals);
        
        return array_merge($returnValues, array(
            'transaction'    => $transaction,
            'delivery'       => $delivery,
            'totals'         => $totals,
            'transfer_form'  => $transferForm->createView(),
            'paypal_form'    => $paypalForm->createView(),
            'redsys_form'    => $redsysForm->createView(),
            'creditcard_form'=> $creditCardform->createView(),
            'braintree_form'=> $braintreeForm->createView(),
            ));
    }
    
     /**
     * @Route("/credit-card")
     * @Template()
     */
    public function creditCardAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        list($actor, $form, $token) = $this->createCCForm($request);

        //check if actor already hace a contract active
        if($this->hasActiveContract($actor)){
            $this->get('session')->getFlashBag()->add(
                'danger',
                'Ya posees un contracto activo por favor, cancélalo primero si quieres cambiar de plan'
            );
        }

        if($request->getMethod() == 'POST'){
            $form->bind($request);
            if ($form->isValid()){
                
                //Create contract and paypal agreement 
               $answer = $this->createContract($actor, $form);
               
               if($answer['state'] == 'Active'){
                    if($token != ''){
                        $token = $em->getRepository('CoreBundle:EmailToken')->findOneByToken($token);
                        if ($token instanceof EmailToken) {
                            $em->remove($token);
                            $em->flush(); 
                        }
                    }
                   return $this->redirect($this->generateUrl('ecommerce_checkout_confirmationpayment'));
               }else{
                   return $this->redirect($this->generateUrl('ecommerce_checkout_cancelationpayment'));
               }
            }
        }

        return array(
            'form' => $form->createView()
                );
           
    }
    
    public function hasActiveContract(Actor $actor) {
        foreach ($actor->getContracts() as $contract) {
            if ($contract->getAgreement()->getStatus() == 'Active'){
                return true;
                
            }
        }
        return false;
    }
    
    /**
     * Creates a form to create a Post entity.
     *
     * @param Post $token string
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreditCardForm()
    {
        $class = new CreditCardForm($this->get('validator'));
        $type = new CreditCardType(array());
        
        $form = $this->createForm($type, $class, array(
            'action' => $this->generateUrl('ecommerce_checkout_summary'),
            'method' => 'POST',
            'attr' => array('id' => 'payment-cc', 'class' => 'cc-form')
        ));
        $form->add('submit', 'submit', array('label' => 'Pagar'));
        
        return $form;
    }
    
    /**
     * Creates a form to create a Post entity.
     *
     * @param Post $token string
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCCForm(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        //Token
        $token = null;
        $promotionCode = null;
        if($request->query->get('token') != ''){
            $token = $request->query->get('token');
            $tokenEntity = $em->getRepository('CoreBundle:EmailToken')->findOneByToken($token);
            $actor =  $em->getRepository('CoreBundle:Actor')->findOneByEmail($tokenEntity->getEmail());
        }else{
            $actor = $this->get('security.token_storage')->getToken()->getUser();
            //Promotion code part
            if($request->query->get('promotionCode') != ''){
                $promotionCode = $request->query->get('promotionCode');
            }
        }
        
        //Config form
        $planConfig = array('plan' => true);
        $url = null;
        if(!is_null($token)) $url = '?token='.$token;
        if(!is_null($actor)) {
            //PromotionCode part
            if(!is_null($promotionCode)){
                 $planConfig['plan'] = $em->getRepository('EcommerceBundle:Plan')->findOneByPaypalId($promotionCode);
                 if(is_null($token))
                     $url .= '?promotionCode='.$promotionCode;
                 else
                     $url .= '&promotionCode='.$promotionCode;
            }else{
                //all actor already have a plan, every pack must be a plan assocc 
                if(is_null($token))
                $planConfig['plan'] = $actor->getPack()->getPlans()->first();
            }
        }
        
        $class = new CreditCardForm($this->get('validator'));
        $type = new CreditCardType($planConfig);
        
        $form = $this->createForm($type, $class, array(
            'action' => $this->generateUrl('ecommerce_checkout_creditcard').$url,
            'method' => 'POST',
            'attr' => array('id' => $type->getName(),'class' => 'cc-form')
        ));
        $form->add('submit', 'submit', array('label' => 'Pagar'));

        return array($actor, $form, $token);
    }
    
    public function createContract($actor, $form) 
    {
        $em = $this->getDoctrine()->getManager();
        //contract
        $contract = new Contract();
        $contract->setFinished(new DateTime('+1 year'));
        $contract->setActor($actor);
        $contract->setUrl('http://localhost/aviso-legal');
        $actor->addContract($contract);
        $em->persist($contract);

        $agree = new Agreement();
        $agree->setPlan($form->getNormData()->plan);
        $agree->setStatus('Created');
        $agree->setPaymentMethod('credit_card');
        $uid = uniqid();
        $agree->setName('Test agreement '.$uid);
        $agree->setDescription('Description of test agreement '.$uid);
        $agree->setContract($contract);
        $contract->setAgreement($agree);
        $em->persist($agree);
        $em->flush(); 

        //4548812049400004//visa//12//2017//123
        //error
        $errors = $this->get('validator')->validate($agree);
        if(count($errors)==0){
            $answer = $this->get('checkout_manager')->createPaypalAgreement($agree, array(
                "number" => $form->getNormData()->cardNo,
                "type" => $form->getNormData()->cardType,
                "expire_month" =>  $form->getNormData()->expirationDate->format('m'),
                "expire_year" =>  $form->getNormData()->expirationDate->format('Y'),
                "cvv2" =>  $form->getNormData()->CVV,
                "first_name" =>  $form->getNormData()->firstname,
                "last_name" =>  $form->getNormData()->lastname
           ));
            
            return $answer;
        }else{
            throw $this->createNotFoundException('Error: '.json_decode($errors));
        }

    }
    /**
     * 
     * @Route("/response-ok")
     * @Template("EcommerceBundle:Checkout:response.ok.html.twig")
     */    
    public function confirmationPaymentAction()
    {
        $em = $this->getDoctrine()->getManager();
        /** @var CheckoutManager $checkoutManager */
        $checkoutManager = $this->get('checkout_manager');
        $delivery = $checkoutManager->getDelivery();
        
        if($this->get('session')->has('agreement')){
            $sessionAgreement = json_decode($this->get('session')->get('agreement'));
            $agreement = $em->getRepository('EcommerceBundle:Agreement')->findOneByPaypalId($sessionAgreement->id);

            if(!$agreement){
                throw $this->createNotFoundException('Unable to find Agreement entity.');
            }
            
            $checkoutManager->cleanSession();
            if($agreement instanceof Agreement){
                return array('agreement_id' => $agreement->getId());
            }
        }
        
        
        //check normal one shot buy
        if( $this->get('request')->get('paypal') != '' && 
            $this->get('request')->get('paymentId') != '' &&
            $this->get('request')->get('PayerID') != '')
            {

            $checkoutManager->paypalToken();
            //Search on transaction paymentDetails the $paymentId
            $paymentId = $this->get('request')->get('paymentId');
            $transaction = $em->getRepository('EcommerceBundle:Transaction')->findOnPaymentDetails($paymentId);

            $payerId = $this->get('request')->get('PayerID');
            $payment_execute = array(
                            'payer_id' => $payerId
                           );
            $json = json_encode($payment_execute);
            
            //Search 'payment_execute_url' in paymentDetails transaction
            $paymentDetails = json_decode($transaction->getPaymentDetails());
            foreach ($paymentDetails->links as $link) {
                if($link->rel == 'execute'){
                        $payment_execute_url = $link->href;
                }
            }
            $json_resp = $checkoutManager->paypalCall('POST', $payment_execute_url, $json);
//            $html  = "Payment Execute processed " . $json_resp['id'] ." with state '". $json_resp['state']."'";
            
            //UPDATE TRANSACTION
            $transaction->setStatus(Transaction::STATUS_PAID);
            $transaction->setPaymentDetails($transaction->getPaymentDetails().json_encode($json_resp));
            $em->flush();

        }
        elseif( $this->get('request')->get('redsys') != ''){
            //do nothing
        }
        //check recurring paypal
        elseif($this->get('request')->get('paypal') != '' &&
                $this->get('request')->get('token') != ''){
            
            $checkoutManager->paypalToken();

            if(!$this->get('session')->has('agreement')){
                return $this->redirect($this->generateUrl('ecommerce_checkout_detail'));
            }

            //Search 'payment_execute_url' in paymentDetails transaction
            $paymentDetails = json_decode($this->get('session')->get('agreement'));
            foreach ($paymentDetails->links as $link) {
                if($link->rel == 'execute'){
                    $payment_execute_url = $link->href;
                }
            }
           
            $json_resp = $checkoutManager->paypalCall('POST', $payment_execute_url, '{}');
            //Recurring payment_method = paypal (agreement execute)
            if(isset($json_resp['state']) && $json_resp['state'] == 'Active'){

                $agreement->setStatus($json_resp['state']);
                $agreement->setPaypalId($json_resp['id']);
                $agreement->setOutstandingAmount($json_resp['agreement_details']['outstanding_balance']['value']);
                $agreement->setCyclesRemaining($json_resp['agreement_details']['cycles_remaining']);
                $agreement->setNextBillingDate($json_resp['agreement_details']['next_billing_date']);
                $agreement->setFinalPaymentDate($json_resp['agreement_details']['final_payment_date']);
                $agreement->setFailedPaymentCount($json_resp['agreement_details']['failed_payment_count']);
                $em->flush();
            }elseif(isset($json_resp['status']) && $json_resp['status'] == 'error'){
                $agreement->setStatus($json_resp['state']);
                $em->flush();
                throw $this->createNotFoundException('Error on confirmation payment:'. json_encode($json_resp));
            }
            $checkoutManager->cleanSession();
            if($agreement instanceof Agreement){
                return array('agreement_id' => $agreement->getId());
            }
        }
        
        //send email
         if ($this->get('session')->has('transaction-id')) {
            /** @var Transaction $transaction */
            $transaction = $em->getRepository('EcommerceBundle:Transaction')->find($this->get('session')->get('transaction-id'));
//            $this->get('checkout_manager')->sendToTransport($transaction);
         }
        

        $urlInvoice = $checkoutManager->getRedirectUrlInvoice($delivery);
        $checkoutManager->cleanSession();

        return array('url_invoice' => $urlInvoice);
    }
   
    /**
     * @Route("/cancel-payment")
     * @Template("EcommerceBundle:Checkout:cancelationPayment.html.twig")
     */    
    public function cancelationPaymentAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var CheckoutManager $checkoutManager */
        $checkoutManager = $this->get('checkout_manager');
        //clean al sessionn vars
        $checkoutManager->cleanSession();
            
        if( $this->get('request')->get('token') != '' ){
            //Search on transaction paymentDetails the $paymentId
            $token = $this->get('request')->get('token');
            $transaction = $em->getRepository('EcommerceBundle:Transaction')->findOnPaymentDetails($token);
            //UPDATE TRANSACTION
            if($transaction instanceof Transaction){
                $transaction->setStatus(Transaction::STATUS_CANCELLED);
                $em->flush();
            }

            $this->get('session')->getFlashBag()->add(
                'danger',
                'transaction.cancel'
            );
            
            return $this->redirect($this->generateUrl('core_profile_index', array('transactions' => true)));
            
        }
        elseif( $this->get('request')->get('Ds_Order') != '' &&
                $this->get('request')->get('Ds_MerchantCode') != '' &&
                $this->get('request')->get('Ds_Terminal') != '')
        {
            //Search on transaction paymentDetails the $paymentId
            $orderId = $this->get('request')->get('Ds_Order');
            $transaction = $em->getRepository('EcommerceBundle:Transaction')->findOnPaymentDetails($orderId);
            $core = $this->container->getParameter('core');
            if(in_array($this->get('kernel')->getEnvironment(), array('test', 'dev'))) {
                $redsysConfig = $core['ecommerce']['redsys']['dev'];
            }else{
                $redsysConfig = $core['ecommerce']['redsys']['prod'];
            }
   
            //UPDATE TRANSACTION
            if($transaction instanceof Transaction &&
               $this->get('request')->get('Ds_MerchantCode') == $redsysConfig['code'] &&
               $this->get('request')->get('Ds_Terminal') == $redsysConfig['terminal']     
            ){
                
                $transaction->setStatus(Transaction::STATUS_CANCELLED);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'danger',
                    'transaction.cancel'
                ); 
                 
                return array();
           }
          
        }
        
        return array();
    }
    
    /**
    * @return \Symfony\Component\Form\Form The form
    */
    private function createResysForm(Transaction $transaction, $totals)
    {
        $em = $this->getDoctrine()->getManager();
        $core = $this->container->getParameter('core');
        $translator = $this->container->get('translator');
        
        if(in_array($this->get('kernel')->getEnvironment(), array('test', 'dev'))) {
            $formConfig = $core['ecommerce']['redsys']['dev'];
        }else{
            $formConfig = $core['ecommerce']['redsys']['prod'];
        }
        
        $formConfig['amount'] = (int) str_replace('.', '', number_format($totals['amount'], 2));
        $formConfig['data'] = $transaction->getTransactionKey();
        $formConfig['name'] = $core['company']['name'];
        $formConfig['product'] = $transaction->getItems()->first()->getProduct()->getName();
        $formConfig['titular'] = $transaction->getActor()->getFullName();
        
        $created = $transaction->getCreated();
        $formConfig['order'] = $created->format('ymdHis'); 

        $hash = $formConfig['amount'].
                $formConfig['order'].
                $formConfig['code'].
                $formConfig['currency'].
                $formConfig['transaction_type'].
                $formConfig['bank_response_url'].
                $formConfig['secret']
                ;
        $formConfig['signature'] = strtoupper(sha1($hash));
        
        //UPDATE TRANSACTION ????
        $transaction->setStatus(Transaction::STATUS_PENDING);
        $transaction->setPaymentMethod(Transaction::PAYMENT_METHOD_CREDIT_CARD);
        $transaction->setPaymentDetails(json_encode(array(
                    'amount' => $formConfig['amount'],
                    'order' => $formConfig['order'],
                    'code' => $formConfig['code'],
                    'currency' => $formConfig['currency'],
                    'transaction_type' => $formConfig['transaction_type'],
                    'bank_response_url' => $formConfig['bank_response_url'],
                    'secret' => $formConfig['secret'],
                    'signature' => $formConfig['signature']
                )));
        $em->flush();
        
        $form = $this->createForm(new RedsysType($formConfig), null, array(
            'action' => $formConfig['host'],
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => $translator->trans('checkout.redsys')));

        return $form;
    }
    
    /**
     * @Route("/redsys-response")
     * 
     */    
    public function redsysResponseAction(Request $request)
    { 
        $order_send =  $request->query->get('Ds_Order');
        $order_num = (int) substr($order_send,0,6);

        $request->getSession()->set('delivery-id',$order_num);
        $logger = $this->get('logger');
        $logger->info('XXXXXXXXXXXXXXXXXXXXDs_Response = '.$request->query->get('Ds_Response'));
        $logger->info('XXXXXXXXXXXXXXXXXXXXDs_Order = '.$request->query->get('Ds_Order'));
        $logger->info('XXXXXXXXXXXXXXXXXXXXNº = '.$order_num);

        
        $em = $this->getDoctrine()->getManager();
        $checkoutManager = $this->get('checkout_manager');
        $core = $this->container->getParameter('core');
        $response = new RedsysResponse($core['ecommerce']['redsys']['secret'], $request);

        $logger->info('XXXXXXXXXXXXXXXXXXXX '.(int) $response->isValidRequest());
        if (!$response->isValidRequest()) {
                $newResponse = new Response();
                $newResponse->setStatusCode(403);
                return $newResponse;
        }

        $transactionKey = $response->getMerchantData();
        $creationDateString = $response->getOrder();
 
        $transaction = $em->getRepository('EcommerceBundle:Transaction')
                ->findByPaymentDetailsAndTransactionKey($creationDateString, $transactionKey);

        $logger->info('XXXXXXXXXXXXXXXXXXXX '.$creationDateString.'-'.$transactionKey);
        
        if (! $transaction) {
             $logger->info('XXXXXXXXXXXXXXXXXXXX NO HAY TRANSACCION');
                $newResponse = new Response();
                $newResponse->setStatusCode(403);
                return $newResponse;
        }

        if ($transaction->getStatus() == Transaction::STATUS_PENDING) {
                if ($response->isAuthorized()) {
                        $checkoutManager->processRedsysTransaction($response->getResponse(), $transaction);
                } else {
                        $transaction->setStatus(Transaction::STATUS_REJECTED);
                }

                $transaction->setPaymentDetails($transaction->getPaymentDetails().$response->getJsonValues());
                $em->flush();
        }
        
        $newResponse = new Response();
        $newResponse->setStatusCode(200);
        return $newResponse;
    }


}
