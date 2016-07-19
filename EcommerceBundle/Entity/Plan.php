<?php

namespace EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CoreBundle\Entity\Timestampable;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Plan Entity class
 *
 * @ORM\Table(name="plan")
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\PlanRepository")
 */
class Plan extends Timestampable
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
     * @ORM\Column(name="paypal_id", type="string")
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
     * @ORM\Column(name="state", type="string")
     */
    private $state;
    
    /**
     * @var string
     *
     * @ORM\Column(name="frequency", type="string")
     */
    private $frequency;
    
    /**
     * @var string
     *
     * @ORM\Column(name="frequency_interval", type="string")
     */
    private $frequencyInterval;
  
    /**
     * @var string
     *
     * @ORM\Column(name="cycles", type="string")
     */
    private $cycles;
    
    /**
     * @var string
     *
     * @ORM\Column(name="setup_amount", type="string", nullable=true)
     */
    private $setupAmount;
    
    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="string", nullable=true)
     */
    private $amount;
    
    /**
     * @var string
     *
     * @ORM\Column(name="trial_frequency", type="string", nullable=true)
     */
    private $trialFrequency;
    
    /**
     * @var string
     *
     * @ORM\Column(name="trial_frequency_interval", type="string", nullable=true)
     */
    private $trialFrequencyInterval;
  
    /**
     * @var string
     *
     * @ORM\Column(name="trial_cycles", type="string", nullable=true)
     */
    private $trialCycles;
    
    /**
     * @var string
     *
     * @ORM\Column(name="trial_amount", type="string", nullable=true)
     */
    private $trialAmount;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Agreement", mappedBy="plan", cascade={"remove"})
     */
    private $agreements;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="viaible", type="boolean")
     */
    private $visible;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;
    
     /**
     * Constructor
     */
    public function __construct()
    {
        $this->setupAmount = '0';
        $this->visible = false;
        $this->active = false;
        $this->agreements = new ArrayCollection();
    }

    public function __toString() {
        return $this->name;
    }
    /**
     * Get id
     *
     * @return Plan
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
     * @return Plan
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
     * @return Plan
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
     * Set setupAmount
     *
     * @param string $setupAmount
     *
     * @return Plan
     */
    public function setSetupAmount($setupAmount) {
        $this->setupAmount = $setupAmount;
        return $this;
    }
    
    /**
     * Get setupAmount
     *
     * @return string
     */
    public function getSetupAmount() {
        return $this->setupAmount;
    }
    
    /**
     * Set frequency
     *
     * @param string $frequency
     *
     * @return Plan
     */
    public function setFrequency($frequency) {
        $this->frequency = $frequency;
        return $this;
    }
    
    /**
     * Get frequency
     *
     * @return string
     */
    public function getFrequency() {
        return $this->frequency;
    }
    
    /**
     * Set frequencyInterval
     *
     * @param string $frequencyInterval
     *
     * @return Plan
     */
    public function setFrequencyInterval($frequencyInterval) {
        $this->frequencyInterval = $frequencyInterval;
        return $this;
    }
    
    /**
     * Get frequencyInterval
     *
     * @return string
     */
    public function getFrequencyInterval() {
        return $this->frequencyInterval;
    }

    /**
     * Set cycles
     *
     * @param string $cycles
     *
     * @return Plan
     */
    public function setCycles($cycles) {
        $this->cycles = $cycles;
        return $this;
    }
    
    /**
     * Get cycles
     *
     * @return string
     */
    public function getCycles() {
        return $this->cycles;
    }

    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return Plan
     */
    public function setAmount($amount) {
        $this->amount = $amount;
        return $this;
    }
    
    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount() {
        return $this->amount;
    }
    
    /**
     * Set trialFrequency
     *
     * @param string $trialFrequency
     *
     * @return Plan
     */
    public function setTrialFrequency($trialFrequency) {
        $this->trialFrequency = $trialFrequency;
        return $this;
    }
    
    /**
     * Get trialFrequency
     *
     * @return string
     */
    public function getTrialFrequency() {
        return $this->trialFrequency;
    }
    
    /**
     * Set trialFrequencyInterval
     *
     * @param string $trialFrequencyInterval
     *
     * @return Plan
     */
    public function setTrialFrequencyInterval($trialFrequencyInterval) {
        $this->trialFrequencyInterval = $trialFrequencyInterval;
        return $this;
    }
    
    /**
     * Get trialFrequencyInterval
     *
     * @return string
     */
    public function getTrialFrequencyInterval() {
        return $this->trialFrequencyInterval;
    }
    
    /**
     * Set trialCycles
     *
     * @param string $trialCycles
     *
     * @return Plan
     */
    public function setTrialCycles($trialCycles) {
        $this->trialCycles = $trialCycles;
        return $this;
    }
    
    /**
     * Get trialCycles
     *
     * @return string
     */
    public function getTrialCycles() {
        return $this->trialCycles;
    }
    
    /**
     * Set trialAmount
     *
     * @param string $trialAmount
     *
     * @return Plan
     */
    public function setTrialAmount($trialAmount) {
        $this->trialAmount = $trialAmount;
        return $this;
    }
    
    /**
     * Get trialAmount
     *
     * @return string
     */
    public function getTrialAmount() {
        return $this->trialAmount;
    }
    
    /**
     * Set state
     *
     * @param string $state
     *
     * @return Plan
     */
    public function setState($state) {
        $this->state = $state;
        return $this;
    }
    
    /**
     * Get state
     *
     * @return Plan
     */
    public function getState() {
        return $this->state;
    }
   
     /**
     * Add agreement
     *
     * @param Agreement $agreement
     *
     * @return Plan
     */
    public function addAgreement(Agreement $agreement)
    {
        $this->agreements->add($agreement);

        return $this;
    }

    /**
     * Remove agreements
     *
     * @param Plan $agreement
     */
    public function removeAgreement(Agreement $agreement)
    {
        $this->agreements->removeElement($agreement);
    }

    /**
     * Get agreements
     *
     * @return ArrayCollection
     */
    public function getAgreements()
    {
        return $this->agreements;
    }

    /**
     * Set active
     *
     * @param string $active
     *
     * @return boolean
     */
    public function setActive($active) {
        $this->active = $active;
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive() {
        return $this->active;
    }
        
    /**
     * Set visible
     *
     * @param string $visible
     *
     * @return boolean
     */
    public function setVisible($visible) {
        $this->visible = $visible;
        return $this;
    }

    /**
     * Get visible
     *
     * @return boolean
     */
    public function getVisible() {
        return $this->visible;
    }

}
