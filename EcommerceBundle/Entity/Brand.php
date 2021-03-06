<?php

namespace EcommerceBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Entity\Timestampable;
use CoreBundle\Entity\Image;
use CoreBundle\Model;
use EcommerceBundle\Entity\Category;

/**
 * Brand Entity class
 *
 * @ORM\Table(name="brand", indexes={@ORM\Index(columns={"slug"})})
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\BrandRepository")
 */
class Brand extends Timestampable
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=255, unique=true)
     */
    private $slug;

    /**
     * @var boolean
     *
     * @ORM\Column(name="available", type="boolean")
     */
    private $available;

    /**
     * @var Image
     *
     * @ORM\OneToOne(targetEntity="CoreBundle\Entity\Image", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $image;
    
    public $removeImage;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Product", mappedBy="brand", cascade={"remove"})
     */
    private $products;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="BrandModel", mappedBy="brand", cascade={"persist", "remove"})
     */
    private $models;

    /**
     * @var Family
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="brands")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true, onDelete="set null")
     */
    private $category;
    
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->models = new ArrayCollection();
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
     *
     * @return Brand
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
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Is available?
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return $this->available;
    }

    /**
     * Set available
     *
     * @param boolean $available
     *
     * @return Product
     */
    public function setAvailable($available)
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Set image
     *
     * @param Image $image
     *
     * @return Brand
     */
    public function setImage(Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return Image
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
     * Add product
     *
     * @param Product $product
     *
     * @return Brand
     */
    public function addProduct(Product $product)
    {
        $this->products->add($product);

        return $this;
    }

    /**
     * Remove product
     *
     * @param Product $product
     */
    public function removeProduct(Product $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * Get products
     *
     * @return ArrayCollection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add model
     *
     * @param Model $model
     *
     * @return Category
     */
    public function addModel(BrandModel $model)
    {
        $this->models->add($model);

        return $this;
    }

    /**
     * Remove model
     *
     * @param Model $model
     */
    public function removeModel(BrandModel $model)
    {
        $this->models->removeElement($model);
    }

    /**
     * Get models
     *
     * @return ArrayCollection
     */
    public function getModels()
    {
        return $this->models;
    }
    
    
    /**
     * set category
     *
     * @param Category $category
     *
     * @return Category
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }
    
    /**
     * set brand
     *
     * @return Brand
     */
    public function getCategory()
    {
        return $this->category;
    }
    
    
    /**
     * Set url
     *
     * @param string $url
     *
     * @return Slider
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}