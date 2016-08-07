<?php

namespace EcommerceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Entity\Actor;
use CoreBundle\Entity\Timestampable;
use EcommerceBundle\Entity\Agreement;
use EcommerceBundle\Entity\Advert;

/**
 * Transaction Entity class
 *
 * @ORM\Table(name="transaction")
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\TransactionRepository")
 */
class Transaction extends Timestampable
{
    const STATUS_CREATED = 'Created';
    const STATUS_PENDING = 'Pending';
    const STATUS_PENDING_TRANSFER = 'Pending transfer';
    const STATUS_PENDING_APPROVAL = 'Pending approval';
    const STATUS_PAID = 'Paid';
    const STATUS_DELIVERED = 'Delivered';    
    const STATUS_COMPLETED = 'Completed'; //¿¿??
    const STATUS_CANCELLED = 'Cancelled';  
    const STATUS_RETURNED = 'Returned'; //¿¿??
    

    const PAYMENT_METHOD_BRAINTREE_CREDIT_CARD = 'braintree_credit_card';
    const PAYMENT_METHOD_CREDIT_CARD = 'credit_card';
    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_METHOD_PAYPAL = 'paypal';
    const PAYMENT_METHOD_STORE_PICKUP = 'store_pickup';

//     * Transaction::saleNoValidate(array(
//    *    'amount'      => '100.00',
//    *    'orderId'    => '123',
//    *    'channel'    => 'MyShoppingCardProvider',
//    *    'creditCard' => array(
//    *         // if token is omitted, the gateway will generate a token
//    *         'token' => 'credit_card_123',
//    *         'number' => '5105105105105100',
//    *         'expirationDate' => '05/2011',
//    *         'cvv' => '123',
//    *    ),
//    *    'customer' => array(
//    *     // if id is omitted, the gateway will generate an id
//    *     'id'    => 'customer_123',
//    *     'firstName' => 'Dan',
//    *     'lastName' => 'Smith',
//    *     'company' => 'Braintree',
//    *     'email' => 'dan@example.com',
//    *     'phone' => '419-555-1234',
//    *     'fax' => '419-555-1235',
//    *     'website' => 'http://braintreepayments.com'
//    *    ),
//    *    'billing'    => array(
//    *      'firstName' => 'Carl',
//    *      'lastName'  => 'Jones',
//    *      'company'    => 'Braintree',
//    *      'streetAddress' => '123 E Main St',
//    *      'extendedAddress' => 'Suite 403',
//    *      'locality' => 'Chicago',
//    *      'region' => 'IL',
//    *      'postalCode' => '60622',
//    *      'countryName' => 'United States of America'
//    *    ),
//    *    'shipping' => array(
//    *      'firstName'    => 'Andrew',
//    *      'lastName'    => 'Mason',
//    *      'company'    => 'Braintree',
//    *      'streetAddress'    => '456 W Main St',
//    *      'extendedAddress'    => 'Apt 2F',
//    *      'locality'    => 'Bartlett',
//    *      'region'    => 'IL',
//    *      'postalCode'    => '60103',
//    *      'countryName'    => 'United States of America'
//    *    ),
//    *    'customFields'    => array(
//    *      'birthdate'    => '11/13/1954'
//    *    )
//    *  )

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="transaction_key", type="string", unique=true)
     * @Assert\NotBlank
     */
    private $transactionKey;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50)
     * @Assert\NotBlank
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="PaymentMethod")
     */
    protected $paymentMethod;
    
    /**
     * @var string
     *
     * @ORM\Column(name="payment_details", type="string", length=10000, nullable=true)
     */
    private $paymentDetails;

    /**
     * @var Actor
     *
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\Actor")
     * @ORM\JoinColumn(nullable=true)
     */
    private $actor;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ProductPurchase", mappedBy="transaction", cascade={"persist", "remove"})
     */
    private $items;

    /**
     * @var Delivery
     *
     * @ORM\OneToOne(targetEntity="Delivery", mappedBy="transaction", cascade={"persist", "remove"})
     */
    private $delivery;

    /**
     * @var Invoice
     *
     * @ORM\OneToOne(targetEntity="Invoice", mappedBy="transaction", cascade={"persist", "remove"})
     */
    private $invoice;

    /**
     * @var integer
     *
     * @ORM\Column(name="vat", type="float", nullable=true)
     */
    private $vat;
    
    /**
     * @var Tax
     *
     * @ORM\Column(name="tax", type="float", nullable=true)
     */
    private $tax;

    /**
     * @var float
     *
     * @ORM\Column(name="total_price", type="float", nullable=true)
     */
    private $totalPrice;

    /**
     * @var boolean
     *
     * @ORM\Column(name="store_pickup_code", type="string", nullable=true)
     */
    private $storePickupCode;
    
    /**
     * @var Plan
     *
     * @ORM\ManyToOne(targetEntity="Agreement", inversedBy="transactions")
     * @ORM\JoinColumn(name="agreement_id", referencedColumnName="id", nullable=true, onDelete="set null")
     * @Assert\NotBlank
     */
    private $agreement;
    
    /**
     * @var Advert
     *
     * @ORM\ManyToOne(targetEntity="EcommerceBundle\Entity\Advert", inversedBy="transactions")
     * @ORM\JoinColumn(name="advert_id", referencedColumnName="id", nullable=true, onDelete="set null")
     * @Assert\NotBlank
     */
    private $advert;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set transactionKey
     *
     * @param integer $transactionKey
     *
     * @return Transaction
     */
    public function setTransactionKey($transactionKey)
    {
        $this->transactionKey = $transactionKey;

        return $this;
    }

    /**
     * Get transactionKey
     *
     * @return integer
     */
    public function getTransactionKey()
    {
        return $this->transactionKey;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Transaction
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set payment method
     *
     * @param PaymentMethod $paymentMethod
     *
     * @return Transaction
     */
    public function setPaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get payment method
     *
     * @return PaymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set payment details
     *
     * @param string $paymentDetails
     *
     * @return Transaction
     */
    public function setPaymentDetails($paymentDetails)
    {
        $this->paymentDetails = $paymentDetails;

        return $this;
    }

    /**
     * Get payment method
     *
     * @return string
     */
    public function getPaymentDetails()
    {
        return $this->paymentDetails;
    }
    
    /**
     * Set actor
     *
     * @param Actor $actor
     *
     * @return Transaction
     */
    public function setActor(Actor $actor)
    {
        $this->actor = $actor;

        return $this;
    }

    /**
     * Get actor
     *
     * @return Actor
     */
    public function getActor()
    {
        return $this->actor;
    }

    
    /**
     * Add item
     *
     * @param OrderItem $item
     *
     * @return ProductPurchase
     */
    public function addItem(ProductPurchase $item)
    {
        $item->setTransaction($this);
        $this->items->add($item);

        return $this;
    }

    /**
     * Remove item
     *
     * @param ProductPurchase $item
     */
    public function removeItem(ProductPurchase $item)
    {
        $this->items->removeElement($item);
    }

    /**
     * Get items
     *
     * @return ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set delivery
     *
     * @param Delivery $delivery
     *
     * @return Transaction
     */
    public function setDelivery(Delivery $delivery)
    {
        $this->delivery = $delivery;

        return $this;
    }

    /**
     * Get delivery
     *
     * @return Delivery
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * Set invoice
     *
     * @param Invoice $invoice
     *
     * @return Transaction
     */
    public function setInvoice(Invoice $invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice
     *
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Set vat
     *
     * @param integer $vat
     *
     * @return Order
     */
    public function setVat($vat)
    {
        $this->vat = $vat;

        return $this;
    }

    /**
     * Get vat
     *
     * @return integer
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * Get tax
     *
     * @return Tax
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set tax
     *
     * @param float $tax
     *
     * @return Transaction
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }
    
    /**
     * Set total price
     *
     * @param integer $totalPrice
     *
     * @return Order
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * Get total price
     *
     * @return integer
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }
    
    
    /**
     * Get storePickupCode
     *
     * @return integer
     */
    public function getStorePickupCode()
    {
        return $this->storePickupCode;
    }

    /**
     * Set storePickupCode
     *
     * @param float $storePickupCode
     *
     * @return ProductPurchase
     */
    public function setStorePickupCode($storePickupCode)
    {
        $this->storePickupCode = $storePickupCode;

        return $this;
    }    

    /**
     * Set agreement
     *
     * @param Agreement $agreement
     *
     * @return Contract
     */
    public function setAgreement(Agreement $agreement)
    {
        $this->agreement = $agreement;

        return $this;
    }

    /**
     * Get agreement
     *
     * @return Contract
     */
    public function getAgreement()
    {
        return $this->agreement;
    }
    
    /**
     * Set advert
     *
     * @param Advert $advert
     *
     * @return Contract
     */
    public function setAdvert(Advert $advert)
    {
        $this->advert = $advert;

        return $this;
    }

    /**
     * Get advert
     *
     * @return Contract
     */
    public function getAdvert()
    {
        return $this->advert;
    }
    
}