<?php

namespace EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Entity\Actor;
use DateTime;

/**
 * Contract Entity class
 *
 * @ORM\Table(name="contract")
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\ContractRepository")
 */
class Contract 
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
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\Actor", inversedBy="contracts")
     * @ORM\JoinColumn(name="actor_id", referencedColumnName="id")
     * @Assert\NotBlank
     */
    private $actor;
    
    
    /**
     * @var Actor
     *
     * @ORM\OneToOne(targetEntity="EcommerceBundle\Entity\Agreement", inversedBy="contract")
     * @ORM\JoinColumn(name="agreement_id", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank
     */
    private $agreement;
    
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", nullable=true)
     */
    private $url;
    
    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="finished", type="datetime")
     */
    private $finished;
    
    public function __construct() {
        $this->created = new DateTime('now');
        $this->finished = new DateTime('+1 year');
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
     * Set actor
     *
     * @param Actor $actor
     *
     * @return Contract
     */
    public function setActor(Actor $actor)
    {
        $this->actor = $actor;

        return $this;
    }

    /**
     * Get actor
     *
     * @return Contract
     */
    public function getActor()
    {
        return $this->actor;
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
     * Set url
     *
     * @param string $url
     *
     * @return Contract
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return Contract
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Timestampable
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }
    
    /**
     * Set finished
     *
     * @param \DateTime $finished
     *
     * @return Timestampable
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * Get finished
     *
     * @return \DateTime
     */
    public function getFinished()
    {
        return $this->finished;
    }



}
