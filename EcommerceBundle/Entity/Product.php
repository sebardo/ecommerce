<?php

namespace EcommerceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Entity\Image;
use CoreBundle\Entity\Timestampable;
use CoreExtraBundle\Entity\Video;
use CoreBundle\Entity\Actor;
use EcommerceBundle\Entity\ProductStats;

/**
 * Product Entity class
 *
 * @ORM\Table(name="product", indexes={@ORM\Index(columns={"created"})})
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\ProductRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Product extends Timestampable
{
    const PRICE_TYPE_FIXED = 0;
    const PRICE_TYPE_PERCENT = 1;
    
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
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank
     */
    private $description;
    
    /**
     * @var string
     *
     * @ORM\Column(name="technical_details", type="text", nullable=true)
     */
    private $technicalDetails;
    
    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"}, unique=true)
     * @ORM\Column(length=255, unique=false)
     */
    private $slug;

    /**
     * @var float
     *
     * @ORM\Column(name="init_price", type="float", nullable=true)
     */
    private $initPrice;
    
    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     * @Assert\NotBlank
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(name="price_type", type="boolean")
     */
    private $priceType;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="discount", type="integer", nullable=true)
     */
    private $discount;
    
    /**
     * @var float
     *
     * @ORM\Column(name="discounted_price", type="float", nullable=true)
     */
    private $discountType;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="stock", type="integer")
     * @Assert\NotBlank
     */
    private $stock;

    /**
     * @var float
     *
     * @ORM\Column(name="weight", type="float", nullable=true)
     */
    private $weight;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="store_pickup", type="boolean")
     */
    private $storePickup;
    
    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=255, nullable=true)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", length=255, nullable=true)
     */
    private $metaTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="text", nullable=true)
     */
    private $metaDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_tags", type="string", length=255, nullable=true)
     */
    private $metaTags;

    /**
     * @ORM\ManyToOne(targetEntity="\CoreBundle\Entity\Actor")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $actor;
    
    /**
     * @var Brand
     *
     * @ORM\ManyToOne(targetEntity="Brand", inversedBy="products")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="id", nullable=true, onDelete="set null")
     * @Assert\NotBlank
     */
    private $brand;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true, onDelete="set null")
     * @Assert\NotBlank
     */
    private $category;

    /**
     * @var Brand
     *
     * @ORM\ManyToOne(targetEntity="BrandModel", inversedBy="products")
     * @ORM\JoinColumn(name="model_id", referencedColumnName="id", nullable=true, onDelete="set null")
     */
    private $model;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AttributeValue", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="product_attributes",
     *                      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *                      inverseJoinColumns={@ORM\JoinColumn(name="attributevalue_id", referencedColumnName="id", onDelete="CASCADE")})
     */
    private $attributeValues;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="FeatureValue", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="product_features",
     *                      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *                      inverseJoinColumns={@ORM\JoinColumn(name="featurevalue_id", referencedColumnName="id", onDelete="CASCADE")})
     */
    private $featureValues;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="CoreBundle\Entity\Image", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="product_images",
     *                      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *                      inverseJoinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id")})
     */
    private $images;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="CoreExtraBundle\Entity\Video", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="product_videos",
     *                      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *                      inverseJoinColumns={@ORM\JoinColumn(name="video_id", referencedColumnName="id")})
     */
    private $videos;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Product")
     * @ORM\JoinTable(name="related_products",
     *                      joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *                      inverseJoinColumns={@ORM\JoinColumn(name="related_product_id", referencedColumnName="id")})
     */
    private $relatedProducts;

    /**
     * @var Tax
     *
     * @ORM\ManyToOne(targetEntity="Tax")
     * @ORM\JoinColumn(name="tax_id", referencedColumnName="id", nullable=true, onDelete="set null")
     */
    private $tax;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="available", type="boolean")
     */
    private $available;

    /**
     * @var boolean
     *
     * @ORM\Column(name="highlighted", type="boolean")
     */
    private $highlighted;

    /**
     * @var boolean
     *
     * @ORM\Column(name="freeTransport", type="boolean")
     */
    private $freeTransport;
    
    private $publishDateRange;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publish_date_from", type="datetime", nullable=true)
     */
    private $publishDateFrom;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publish_date_to", type="datetime", nullable=true)
     */
    private $publishDateTo;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted", type="datetime", nullable=true)
     */
    private $deleted;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\EcommerceBundle\Entity\ProductStats", mappedBy="product", cascade={"persist", "remove"})
     */
    private $stats;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributeValues = new ArrayCollection();
        $this->featureValues = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->relatedProducts = new ArrayCollection();
        $this->stats = new ArrayCollection();
        $this->active = false;
        $this->freeTransport = false;
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get $technicalDetails
     *
     * @return string
     */
    public function getTechnicalDetails()
    {
        return $this->technicalDetails;
    }

    /**
     * Set technicalDetails
     *
     * @param string $technicalDetails
     *
     * @return Product
     */
    public function setTechnicalDetails($technicalDetails)
    {
        $this->technicalDetails = $technicalDetails;

        return $this;
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

    /**
     * Get initPrice
     *
     * @return float
     */
    public function getInitPrice()
    {
        $initPrice = $this->initPrice;

        return $initPrice;
    }

    /**
     * Set initPrice
     *
     * @param float $initPrice
     *
     * @return Product
     */
    public function setInitPrice($initPrice)
    {
        $this->initPrice = $initPrice;

        return $this;
    }
    
    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        $price = $this->price;

        return $price;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }
    
    /**
     * Get priceType
     *
     * @return float
     */
    public function getPriceType()
    {
        $priceType = $this->priceType;

        return $priceType;
    }

    /**
     * Set priceType
     *
     * @param float $priceType
     *
     * @return Product
     */
    public function setPriceType($priceType)
    {
        $this->priceType = $priceType;

        return $this;
    }

    /**
     * Get discount
     *
     * @return integer
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set discount
     *
     * @param integer $discount
     *
     * @return Product
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }
    
    /**
     * Get discountType
     *
     * @return integer
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * Set $discountType
     *
     * @param integer $discountType
     *
     * @return Product
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;

        return $this;
    }
    
    /**
     * Get stock
     *
     * @return integer
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set stock
     *
     * @param integer $stock
     *
     * @return Product
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }
    
    /**
     * Get weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set weight
     *
     * @param float $weight
     *
     * @return Product
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }
    
    /**
     * Get storePickup
     *
     * @return float
     */
    public function getStorePickup()
    {
        return $this->storePickup;
    }

    /**
     * Set storePickup
     *
     * @param float $storePickup
     *
     * @return Product
     */
    public function setStorePickup($storePickup)
    {
        $this->storePickup = $storePickup;

        return $this;
    }    
    
    /**
     * Get reference
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set reference
     *
     * @param string $reference
     *
     * @return Product
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    

    /**
     * Get metaTitle
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * Set metaTitle
     *
     * @param string $metaTitle
     *
     * @return Product
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * Get metaDescription
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Set metaDescription
     *
     * @param string $metaDescription
     *
     * @return Product
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * Get metaTags
     *
     * @return string
     */
    public function getMetaTags()
    {
        return $this->metaTags;
    }

    /**
     * Set metaTags
     *
     * @param string $metaTags
     *
     * @return Product
     */
    public function setMetaTags($metaTags)
    {
        $this->metaTags = $metaTags;

        return $this;
    }

    /**
     * Set actor
     *
     * @param Actor $actor
     *
     * @return Actor
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
     * Get brand
     *
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set brand
     *
     * @param Brand $brand
     *
     * @return Product
     */
    public function setBrand(Brand $brand = null)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set category
     *
     * @param Category $category
     *
     * @return Product
     */
    public function setCategory(Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    
    /**
     * Get model
     *
     * @return BrandModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set model
     *
     * @param BrandModel $model
     *
     * @return Product
     */
    public function setModel(BrandModel $model = null)
    {
        $this->model = $model;

        return $this;
    }
    
    /**
     * Add attribute value
     *
     * @param AttributeValue $attributeValue
     *
     * @return Product
     */
    public function addAttributeValue(AttributeValue $attributeValue)
    {
        $this->attributeValues->add($attributeValue);

        return $this;
    }

    /**
     * Remove attribute value
     *
     * @param AttributeValue $attributeValue
     */
    public function removeAttributeValue(AttributeValue $attributeValue)
    {
        $this->attributeValues->removeElement($attributeValue);
    }

    /**
     * Get attribute values
     *
     * @return ArrayCollection
     */
    public function getAttributeValues()
    {
        return $this->attributeValues;
    }

    /**
     * Add feature value
     *
     * @param FeatureValue $featureValue
     *
     * @return Product
     */
    public function addFeatureValue(FeatureValue $featureValue)
    {
        $this->featureValues->add($featureValue);

        return $this;
    }

    /**
     * Remove feature value
     *
     * @param FeatureValue $featureValue
     */
    public function removeFeatureValue(FeatureValue $featureValue)
    {
        $this->featureValues->removeElement($featureValue);
    }

    /**
     * Get feature values
     *
     * @return ArrayCollection
     */
    public function getFeatureValues()
    {
        return $this->featureValues;
    }

    /**
     * Add image
     *
     * @param Image $image
     *
     * @return Product
     */
    public function addImage(Image $image)
    {
        $this->images->add($image);

        return $this;
    }

    /**
     * Remove image
     *
     * @param Image $image
     */
    public function removeImage(Image $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return ArrayCollection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Add video
     *
     * @param Video $video
     *
     * @return Product
     */
    public function addVideo(Video $video)
    {
        $this->videos->add($video);

        return $this;
    }

    /**
     * Remove video
     *
     * @param Video $video
     */
    public function removeVideo(Video $video)
    {
        $this->videos->removeElement($video);
    }

    /**
     * Get videos
     *
     * @return ArrayCollection
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * Add related Product
     *
     * @param Product $relatedProduct
     *
     * @return Product
     */
    public function addRelatedProduct(Product $relatedProduct)
    {
        $this->relatedProducts->add($relatedProduct);

        return $this;
    }

    /**
     * Remove relatedProduct
     *
     * @param Product $relatedProduct
     */
    public function removeRelatedProduct(Product $relatedProduct)
    {
        $this->relatedProducts->removeElement($relatedProduct);
    }

    /**
     * Get relatedProducts
     *
     * @return ArrayCollection
     */
    public function getRelatedProducts()
    {
        return $this->relatedProducts;
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
     * @param Tax $tax
     *
     * @return Product
     */
    public function setTax(Tax $tax = null)
    {
        $this->tax = $tax;

        return $this;
    }
    
    
    /**
     * Is active?
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Product
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
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
     * Is highlighted?
     *
     * @return boolean
     */
    public function isHighlighted()
    {
        return $this->highlighted;
    }

    /**
     * Set highlighted
     *
     * @param boolean $highlighted
     *
     * @return Product
     */
    public function setHighlighted($highlighted)
    {
        $this->highlighted = $highlighted;

        return $this;
    }

    /**
     * Is free Transport?
     *
     * @return boolean
     */
    public function isFreeTransport()
    {
        return $this->freeTransport;
    }

    /**
     * Set Free Transport
     *
     * @param boolean $freeTransport
     *
     * @return Product
     */
    public function setFreeTransport($freeTransport)
    {
        $this->freeTransport = $freeTransport;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getPublishDateRange()
    {
        $from = new \DateTime();
        $to = clone $from;
//        $dateString = $from->format('d/m/Y').' '.$to->format('d/m/Y');
        $dateString = '';
        if($this->publishDateFrom != '' && $this->publishDateTo != '')
        $dateString = $this->publishDateFrom->format('d/m/Y').' '.$this->publishDateTo->format('d/m/Y');
        return $dateString;
    }

    /**
     * @param string $publishDateRange
     */
    public function setPublishDateRange($publishDateRange)
    {
        if($publishDateRange != ''){
            $arr = explode(' ', $publishDateRange);
            $this->publishDateFrom = \DateTime::createFromFormat('d/m/Y', $arr[0]);
            $this->publishDateTo = \DateTime::createFromFormat('d/m/Y', $arr[1]);
        }
    }
    
    /**
     * @return \DateTime
     */
    public function getPublishDateFrom()
    {
        return $this->publishDateFrom;
    }

    /**
     * @param \DateTime $publishDateFrom
     */
    public function setPublishDateFrom($publishDateFrom)
    {
        $this->publishDateFrom = $publishDateFrom;
    }
    
    /**
     * @return \DateTime
     */
    public function getPublishDateTo()
    {
        return $this->publishDateTo;
    }

    /**
     * @param \DateTime $publishDateTo
     */
    public function setPublishDateTo($publishDateTo)
    {
        $this->publishDateTo = $publishDateTo;
    }
    
    /**
     * @return \DateTime
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param \DateTime $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }
    
    
    /**
     * Add stat
     *
     * @param ProductStats $stat
     *
     * @return Product
     */
    public function addStat(ProductStats $stat)
    {
        $this->stats->add($stat);

        return $this;
    }

    
    /**
     * Remove stat
     *
     * @param ProductStats $stat
     */
    public function removeStat(ProductStats $stat)
    {
        $this->stats->removeElement($stat);
    }

    /**
     * Get stats
     *
     * @return ArrayCollection
     */
    public function getStats()
    {
        return $this->stats;
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }
}