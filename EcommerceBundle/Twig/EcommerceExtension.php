<?php

namespace EcommerceBundle\Twig;

use Twig_SimpleFunction;
use EcommerceBundle\Entity\CartItem;
use EcommerceBundle\Entity\Product;
use EcommerceBundle\Form\CartItemSimpleType;
use EcommerceBundle\Entity\Transaction;
use DateTime;

/**
 * Class EcommerceExtension
 */
class EcommerceExtension extends \Twig_Extension
{
    private $parameters;

    private $container;

    public function setContainer($container)
    {
        $this->container = $container;
    }
    
    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        if(isset($parameters['parameters'])) $this->parameters = $parameters['parameters'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('cart_exists', array($this, 'hasCart')),
            new Twig_SimpleFunction('cart_get', array($this, 'getCurrentCart')),
            new Twig_SimpleFunction('cart_form', array($this, 'getItemFormView')),
            new Twig_SimpleFunction('shareUrl', array($this, 'shareUrl')),
            new Twig_SimpleFunction('get_approval_link', array($this, 'getApprovalLink')),
            new Twig_SimpleFunction('get_adverts', array($this, 'getAdverts')),
            new Twig_SimpleFunction('get_advert', array($this, 'getAdvert')),
            new Twig_SimpleFunction('get_product_stats', array($this, 'getProductStats')),
        );
    }
    
    public function getAdverts($section=null, $postalCode=null, $city=null, $brand=null) {
        $session = $this->container->get('session');
        $geolocated = $session->get('geolocated');
        if(is_null($postalCode)){
            $postalCode = $session->get('postalCode');
        }elseif(!$postalCode){
            $postalCode = null;
        }
        if(is_null($city)){
            $city = $session->get('city');
        }elseif(!$city){
            $city = null;
        }
                
        $em = $this->container->get('doctrine')->getManager();
        $today = new DateTime();
        $start = $today->format('Y-m-d H:i:s');
        $end = $today->format('Y-m-d'). ' 23:59:59';
        $adverts = $em->getRepository('EcommerceBundle:Advert')
                ->getAdverts($start, $end, $section, $geolocated, $postalCode, $city, $brand);
        
        return $adverts;
    }
    
    public function getAdvert($section=null, $postalCode=null, $city=null, $brand=null) {
         
        $adverts = $this->getAdverts($section, $postalCode, $city, $brand);
        $advert = $adverts->get(array_rand($adverts->toArray()));
        
        return $advert;
    }
    
    public function getApprovalLink(Transaction $transaction) {
        $details = $transaction->getPaymentDetails();
        $answer = json_decode($details, TRUE);
        if(isset($answer['links'])) {
            foreach ($answer['links'] as $links) {
                if(isset($links['href'])  && 
                    isset($links['rel']) && 
                    $links['rel'] == 'approval_url') return $links['href'];
            }
        }
        return false;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('price', array($this, 'priceFilter')),
            new \Twig_SimpleFilter('prices', array($this, 'pricesFilter')),
        );
    }

    /**
     * Returns current cart.
     *
     * @return CartInterface
     */
    public function getCurrentCart()
    {
        return $this->container->get('cart_provider')->getCart();
    }

    /**
     * Check if a cart exists.
     *
     * @return boolean
     */
    public function hasCart()
    {
        return $this->container->get('cart_provider')->hasCart();
    }

    /**
     * Returns cart item form view.
     *
     * @param array $options
     *
     * @return FormView
     */
    public function getItemFormView(array $options = array())
    {
        $item = new CartItem();
        $form = $this->container->get('form.factory')->create(new CartItemSimpleType(), $item, $options);

        return $form->createView();
    }
    
    /**
     * Price filter
     *
     * @param int    $number
     * @param bool   $applyOtherPercentageCharge
     * @param bool   $applyVat
     * @param bool   $round
     * @param int    $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     *
     * @return string
     */
    public function pricesFilter($number, $type)
    {
        //€ = 0
        //% = 1
        // apply format
        $price = number_format($number, 2, ',', '.');

        // remove decimals when they are ,00
        if ('00' === substr($price, -2)) {
            $price = substr($price, 0, -3);
        }

        if($type){
            $price = $price.' %';
        }else{
            $price = $price.' '.$this->parameters['ecommerce']['currency_symbol'];
        }
        
        

        return $price;
    }
    
    /**
     * Price filter
     *
     * @param int    $number
     * @param bool   $applyOtherPercentageCharge
     * @param bool   $applyVat
     * @param bool   $round
     * @param int    $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     *
     * @return string
     */
    public function priceFilter($number, $applyOtherPercentageCharge = false, $applyVat = false, $round = false, $decimals = 2, $decPoint = ',', $thousandsSep = '.', $applyNewVat = 0)
    {
//        currency_symbol: app.currency_symbol 
//        vat: app.vat 
//        special_percentage_charge: app.special_percentage_charge

        // apply VAT
        if($applyVat){
            // apply new vat inline
            if($applyNewVat==0){
                $price = $applyVat ? $number * (1 + $this->parameters['company']['vat'] / 100) : $number;
            }else{
                $price = $number * (1 + $applyNewVat / 100);
            }   
        }else{
            $price = $number;
        }
        
        

        // apply other percentage charge
        if ($applyOtherPercentageCharge) {
            $price = $price * (1 + $this->parameters['company']['special_percentage_charge'] / 100);
        }

        
        
        // round
        if ($round) {
            $price = round($price);
        }

        // apply format
        $price = number_format($price, $decimals, $decPoint, $thousandsSep);

        // remove decimals when they are ,00
        if ('00' === substr($price, -2)) {
            $price = substr($price, 0, -3);
        }

        $price = $price.' '.$this->parameters['ecommerce']['currency_symbol'];

        return $price;
    }
    
    /**
    * Returns the part of a feedID
    *
    * @param string $feedID  ID of the feed to load
    */
    public function shareUrl($url, $big=false)
    {
        if (!is_numeric(strpos($url, 'http'))) {
            $core = $this->container->getParameter('core');
            $url = $core['server_base_url'] . $url;
        }
        $text = 'Check out this site:';
        $tweetUrl =  'https://twitter.com/share?url='.$url.'&counturl='.$url.'&text='.$text;
        $faceUrl = 'http://www.facebook.com/sharer/sharer.php?u='.$url.'&text='.$text;
        $googleUrl = 'https://plus.google.com/share?url='.$url;
        $linkedUrl = 'https://www.linkedin.com/shareArticle?summary=&ro=false&title='.$text.'&mini=true&url='.$url.'&source=';

        $twig = $this->container->get('twig');
        $content = $twig->render('EcommerceBundle:Product:share.html.twig', array(
            "tweetUrl" => $tweetUrl,
            "faceUrl" => $faceUrl,
            "googleUrl" => $googleUrl,
            "linkedUrl" => $linkedUrl,
            "id" => uniqid(),
            "big" => $big
        ));

        return $content;
    }
    
    /**
    * Returns statistics from product
    *
    */
    public function getProductStats(Product $product, $start, $end)
    {
         /** @var CheckoutManager $checkoutManager */
        $adminManager =  $this->container->get('checkout_manager');
        $stats = $adminManager->getProductStats($product, $start, $end);

        
        return  $stats;
    }
    
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ecommerce_extension';
    }
}