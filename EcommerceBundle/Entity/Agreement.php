<?php

namespace EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Entity\Timestampable;
use EcommerceBundle\Entity\Plan;
use EcommerceBundle\Entity\Contract;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Agreement Entity class
 *
 * @ORM\Table(name="agreement")
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\AgreementRepository")
 */
class Agreement extends Timestampable
{
    
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
     * @ORM\Column(name="paypal_id", type="string", nullable=true)
     */
    private $paypalId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description;
    
    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50)
     * @Assert\NotBlank
     */
    private $status;
    
    /**
     * @var Plan
     *
     * @ORM\ManyToOne(targetEntity="Plan", inversedBy="agreements")
     * @ORM\JoinColumn(name="plan_id", referencedColumnName="id", nullable=true, onDelete="set null")
     * @Assert\NotBlank
     */
    private $plan;
    
    /**
     * @ORM\OneToOne(targetEntity="\EcommerceBundle\Entity\Contract", mappedBy="agreement")
     */
    private $contract;
    
    /**
     * @var string
     *
     * @ORM\Column(name="payment_method", type="string", length=100, nullable=true)
     */
    private $paymentMethod;
    
    /**
     * @var string
     *
     * @ORM\Column(name="outstanding_amount", type="string", nullable=true)
     */
    private $outstandingAmount;
    
    /**
     * @var string
     *
     * @ORM\Column(name="cycles_remaining", type="string", nullable=true)
     */
    private $cyclesRemaining;
    
    /**
     * @var string
     *
     * @ORM\Column(name="cycles_completed", type="string", nullable=true)
     */
    private $cyclesCompleted;
    
    /**
     * @var string
     *
     * @ORM\Column(name="next_billing_date", type="string", nullable=true)
     */
    //ISO format 2014-02-19T10:00:00Z 
    private $nextBillingDate;
    
    /**
     * @var string
     *
     * @ORM\Column(name="last_payment_date", type="string", nullable=true)
     */
    //ISO format 2014-02-19T10:00:00Z 
    private $lastPaymentDate;
    
    /**
     * @var string
     *
     * @ORM\Column(name="last_payment_amount", type="string", nullable=true)
     */
    private $lastPaymentAmount;
    
    /**
     * @var string
     *
     * @ORM\Column(name="final_payment_date", type="string", nullable=true)
     */
    //ISO format 2014-02-19T10:00:00Z 
    private $finalPaymentDate;
    
    /**
     * @var string
     *
     * @ORM\Column(name="failedPaymentCount", type="string", nullable=true)
     */
    private $failedPaymentCount;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="agreement", cascade={"remove"})
     */
    private $transactions;
    
    private $creditCard;
    
     /**
     * Constructor
     */
    public function __construct()
    {
        $this->cyclesCompleted=0;
        $this->transactions = new ArrayCollection();
    }
    
    public function __toString() {
       return $this->name;
    }
    
    /**
     * Get id
     *
     * @return id
     */
    public function getId() {
        return $this->id;
    }
     
    /**
     * Set paypalId
     *
     * @param string $paypalId
     *
     * @return Plan
     */
    public function setPaypalId($paypalId)
    {
        $this->paypalId = $paypalId;

        return $this;
    }
    
    /**
     * Get paypalId
     *
     * @return paypalId
     */
    public function getPaypalId() {
        return $this->paypalId;
    }
    
    /**
     * Set name
     *
     * @param string $name
     *
     * @return Agreement
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
    
    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    
    /**
     * Set description
     *
     * @param string $description
     *
     * @return Agreement
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }
    
    /**
     * Get description
     *
     * @return string
     */
    public function getDescription() {
        return $this->description;
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
     * Set plan
     *
     * @param Plan $plan
     *
     * @return Contract
     */
    public function setPlan(Plan $plan)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get plan
     *
     * @return Contract
     */
    public function getPlan()
    {
        return $this->plan;
    }
    
    /**
     * Set contract
     *
     * @param Contract $contract
     *
     * @return Contract
     */
    public function setContract(Contract $contract)
    {
        $this->contract = $contract;

        return $this;
    }

    /**
     * Get contract
     *
     * @return Contract
     */
    public function getContract()
    {
        return $this->contract;
    }
    
    /**
     * Set payment method
     *
     * @param string $paymentMethod
     *
     * @return Transaction
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get payment method
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set outstandingAmount
     *
     * @param string $outstandingAmount
     *
     * @return Agreement
     */
    public function setOutstandingAmount($outstandingAmount) {
        $this->outstandingAmount = $outstandingAmount;
        return $this;
    }
    
    /**
     * Get outstandingAmount
     *
     * @return string
     */
    public function getOutstandingAmount() {
        return $this->outstandingAmount;
    }

    /**
     * Set cyclesRemaining
     *
     * @param string $cyclesRemaining
     *
     * @return Agreement
     */
    public function setCyclesRemaining($cyclesRemaining) {
        $this->cyclesRemaining = $cyclesRemaining;
        return $this;
    }
    
    /**
     * Get cyclesRemaining
     *
     * @return string
     */
    public function getCyclesRemaining() {
        return $this->cyclesRemaining;
    }

    /**
     * Set cyclesCompleted
     *
     * @param string $cyclesCompleted
     *
     * @return Agreement
     */
    public function setCyclesCompleted($cyclesCompleted) {
        $this->cyclesCompleted = $cyclesCompleted;
        return $this;
    }
    
    /**
     * Get cyclesCompleted
     *
     * @return string
     */
    public function getCyclesCompleted() {
        return $this->cyclesCompleted;
    }

    /**
     * Set nextBillingDate
     *
     * @param string $nextBillingDate
     *
     * @return Agreement
     */
    public function setNextBillingDate($nextBillingDate) {
        $this->nextBillingDate = $nextBillingDate;
        return $this;
    }
    
    /**
     * Get nextBillingDate
     *
     * @return string
     */
    public function getNextBillingDate() {
        return $this->nextBillingDate;
    }
    
    /**
     * Set lastPaymentDate
     *
     * @param string $lastPaymentDate
     *
     * @return Agreement
     */
    public function setLastPaymentDate($lastPaymentDate) {
        $this->lastPaymentDate = $lastPaymentDate;
        return $this;
    }

    /**
     * Get lastPaymentDate
     *
     * @return string
     */
    public function getLastPaymentDate() {
        return $this->lastPaymentDate;
    }

    /**
     * Set lastPaymentAmount
     *
     * @param string $lastPaymentAmount
     *
     * @return Agreement
     */
    public function setLastPaymentAmount($lastPaymentAmount) {
        $this->lastPaymentAmount = $lastPaymentAmount;
        return $this;
    }
    
    /**
     * Get lastPaymentAmount
     *
     * @return string
     */
    public function getLastPaymentAmount() {
        return $this->lastPaymentAmount;
    }

    /**
     * Set finalPaymentDate
     *
     * @param string $finalPaymentDate
     *
     * @return Agreement
     */
    public function setFinalPaymentDate($finalPaymentDate) {
        $this->finalPaymentDate = $finalPaymentDate;
        return $this;
    }
    
    /**
     * Get finalPaymentDate
     *
     * @return string
     */
    public function getFinalPaymentDate() {
        return $this->finalPaymentDate;
    }

    /**
     * Set failedPaymentCount
     *
     * @param string $failedPaymentCount
     *
     * @return Agreement
     */
    public function setFailedPaymentCount($failedPaymentCount) {
        $this->failedPaymentCount = $failedPaymentCount;
        return $this;
    }
    
    /**
     * Get failedPaymentCount
     *
     * @return string
     */
    public function getFailedPaymentCount() {
        return $this->failedPaymentCount;
    }

    /**
     * Add transaction
     *
     * @param Transaction $transaction
     *
     * @return Agreement
     */
    public function addTransaction(Transaction $transaction)
    {
        $transaction->setAgreement($this);
        $this->transactions->add($transaction);

        return $this;
    }

    /**
     * Remove transaction
     *
     * @param Transaction $transaction
     */
    public function removeTransaction(Transaction $transaction)
    {
        $this->transactions->removeElement($transaction);
    }

    /**
     * Get transactions
     *
     * @return ArrayCollection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }
    
    /**
     * Set creditCard
     *
     * @param string $creditCard
     *
     * @return Plan
     */
    public function setCreditCard($creditCard)
    {
        $this->creditCard = $creditCard;

        return $this;
    }
    
    /**
     * Get creditCard
     *
     * @return creditCard
     */
    public function getCreditCard() {
        return $this->creditCard;
    }
    
}
