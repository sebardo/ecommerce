<?php

namespace EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use CoreBundle\Entity\Actor;
use EcommerceBundle\Entity\Located;
use EcommerceBundle\Entity\Rangeable;
use CoreBundle\Entity\PostalCode;
use EcommerceBundle\Entity\Transaction;
use EcommerceBundle\Entity\Brand;

/**
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\AdvertRepository")
 * @ORM\Table(name="advert")
 */
class Advert  extends Rangeable
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(name="title", type="string", length=140)
     */
    protected $title;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;
    
     /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $days;
    
    /**
     * @var Image
     *
     * @ORM\OneToOne(targetEntity="\EcommerceBundle\Entity\AdvertImage", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $image;
    
    public $removeImage;
    
    /**
     * @Gedmo\Slug(fields={"title"}, updatable=true, separator="-")
     * @ORM\Column(name="slug", type="string", length=140)
     */
    protected $slug;

    /**
     * @ORM\ManyToOne(targetEntity="\CoreBundle\Entity\Actor")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $actor;

    /**
     * @ORM\ManyToMany(targetEntity="\EcommerceBundle\Entity\Located", inversedBy="adverts")
     * @ORM\JoinTable(name="adverts_located",
     *      joinColumns={@ORM\JoinColumn(name="advert_id", referencedColumnName="id", nullable=true)},
     *      inverseJoinColumns={@ORM\JoinColumn(name="located_id", referencedColumnName="id")}
     *      )
     */
    protected $located;
    
    private $creditCard;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\EcommerceBundle\Entity\Transaction", mappedBy="advert", cascade={"remove"})
     */
    private $transactions;
    
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
        $this->located = new ArrayCollection();
        $this->active = false;
        $this->transactions = new ArrayCollection();
    }
    
    /**
     * Returns the name
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
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
     * Set title
     *
     * @param  string $title
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        
        return $this;
    }

    /**
     * Get description
     *
     * @return text
     */
    public function getDescription()
    {
        return $this->description;
    }
   
    
    /**
     * Get days
     *
     * @return string
     */
    public function getDays()
    {
        return $this->days;
    }
    
    /**
     * Set days
     *
     * @param string $days
     * @return $this
     */
    public function setDays($days)
    {
        $this->days = $days;

        return $this;
    }
    
    /**
     * Set image
     *
     * @param string $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }
    
    public function setRemoveImage($removeImage)
    {
        $this->removeImage = $removeImage;

        return $this->removeImage;
    }

    public function getRemoveImage()
    {
        return $this->removeImage;
    }
    
    /**
     * Set slug
     *
     * @param  string $slug
     * @return Advert
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set actor
     *
     * @param  Actor $actor
     * @return Advert
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
     * Add located
     *
     * @param Located $located
     *
     * @return Advert
     */
    public function addLocated(Located $located)
    {
        $this->located[] = $located;

        return $this;
    }

    /**
     * Remove located
     *
     * @param Located $located
     */
    public function removeLocated(Located $located)
    {
        $this->located->removeElement($located);
    }

    /**
     * Get located
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLocated()
    {
        return $this->located;
    }
    
     /**
     * Set creditCard
     *
     * @param string $creditCard
     *
     * @return Advert
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
     * Add transaction
     *
     * @param Transaction $transaction
     *
     * @return Advert
     */
    public function addTransaction(Transaction $transaction)
    {
        $transaction->setAdvert($this);
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
   
}
