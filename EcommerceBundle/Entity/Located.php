<?php
namespace EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\LocatedRepository")
 * @ORM\Table(name="advert_located")
 */
class Located
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;
 
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $height;
    
    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $width;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $active = true;

    /**
    * @ORM\ManyToMany(targetEntity="Advert", mappedBy="located")
    */
    protected $adverts;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"}, unique=true)
     * @ORM\Column(length=255, unique=false)
     */
    private $slug;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->adverts = new ArrayCollection();
    }

    /**
     * Returns the name
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
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
     * Set name
     *
     * @param string $name
     *                     @return $this
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
     * Set height
     *
     * @param string $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return string
     */
    public function getHeight()
    {
        return $this->height;
    }
    
    /**
     * Set width
     *
     * @param string $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return string
     */
    public function getWidth()
    {
        return $this->width;
    }

   
    /**
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Add advert
     *
     * @param Adverttising $advert
     *
     * @return Located
     */
    public function addAdvert(Advert $advert)
    {
        $this->adverts[] = $advert;

        return $this;
    }

    /**
     * Remove $advert
     *
     * @param Advert $advert
     */
    public function removeAdvert(Advert $advert)
    {
        $this->adverts->removeElement($advert);
    }

    /**
     * Get adverts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdverts()
    {
        return $this->adverts;
    } 
    
    /**
     * Get slug
     *
     * @return string
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

    
}
