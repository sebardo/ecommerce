<?php

namespace EcommerceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use CoreBundle\Entity\Timestampable;
use CoreBundle\Entity\Image;

/**
 * Category Entity class
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\CategoryRepository")
 * @Assert\Callback(methods={"validateFamilyAndParentCategory"})
 */
class Category extends Timestampable
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
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="assembly_price", type="integer", nullable=true)
     */
    private $assemblyPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", length=255)
     * @Assert\NotBlank
     */
    private $metaTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="text")
     * @Assert\NotBlank
     */
    private $metaDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_tags", type="string", length=255, nullable=true)
     */
    private $metaTags;

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
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parentCategory", cascade={"remove"})
     */
    private $subcategories;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="subcategories")
     * @ORM\JoinColumn(name="parent_category_id", referencedColumnName="id", nullable=true , onDelete="cascade")
     */
    private $parentCategory;

    /**
     * @var Family
     *
     * @ORM\ManyToOne(targetEntity="Family", inversedBy="categories")
     * @ORM\JoinColumn(name="family_id", referencedColumnName="id", nullable=true)
     */
    private $family;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Attribute", mappedBy="category", cascade={"persist", "remove"})
     */
    private $attributes;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Feature", mappedBy="category", cascade={"persist", "remove"})
     */
    private $features;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Product", mappedBy="category", cascade={"remove"})
     */
    private $products;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Product")
     * @ORM\JoinTable(name="category_product_assembly",
     *                      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")},
     *                      inverseJoinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")})
     */
    private $categoryProductAssembly;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="_order", type="integer", nullable=true)
     */
    private $order;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Brand", mappedBy="category", cascade={"persist", "remove"})
     */
    private $brands;
    
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
        $this->active = false;
        $this->subcategories = new ArrayCollection();
        $this->attributes = new ArrayCollection();
        $this->features = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->brands = new ArrayCollection();
        $this->categoryProductAssembly = new ArrayCollection();
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
     * @return Category
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
     * Set slug
     *
     * @param string $slug
     *
     * @return Category
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
     * Set description
     *
     * @param string $description
     *
     * @return Category
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * Set assembly price
     *
     * @param int $assemblyPrice
     *
     * @return Category
     */
    public function setAssemblyPrice($assemblyPrice)
    {
        $this->assemblyPrice = $assemblyPrice;

        return $this;
    }

    /**
     * Get assembly price
     *
     * @return int
     */
    public function getAssemblyPrice()
    {
        return $this->assemblyPrice;
    }

    /**
     * Set metaTitle
     *
     * @param string $metaTitle
     *
     * @return Category
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;

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
     * Set metaDescription
     *
     * @param string $metaDescription
     *
     * @return Category
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

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
     * Set metaTags
     *
     * @param string $metaTags
     *
     * @return Category
     */
    public function setMetaTags($metaTags)
    {
        $this->metaTags = $metaTags;

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
     * Set image
     *
     * @param Image $image
     *
     * @return Category
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
     * Add subcategory
     *
     * @param Category $subcategory
     *
     * @return Category
     */
    public function addSubcategory(Category $subcategory)
    {
        $this->subcategories->add($subcategory);

        return $this;
    }

    /**
     * Remove subcategory
     *
     * @param Category $subcategory
     */
    public function removeSubcategory(Category $subcategory)
    {
        $this->subcategories->removeElement($subcategory);
    }

    /**
     * Get subcategories
     *
     * @return ArrayCollection
     */
    public function getSubcategories()
    {
        return $this->subcategories;
    }

    /**
     * Set parent Category
     *
     * @param Category $parentCategory
     *
     * @return Category
     */
    public function setParentCategory(Category $parentCategory = null)
    {
        $this->parentCategory = $parentCategory;

        return $this;
    }

    /**
     * Get parent Category
     *
     * @return Category
     */
    public function getParentCategory()
    {
        return $this->parentCategory;
    }

    /**
     * Set Family
     *
     * @param Family $family
     *
     * @return Category
     */
    public function setFamily(Family $family = null)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * Get Family
     *
     * @return Family
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * Custom validator to check family and parent category exclusion
     *
     * @param ExecutionContextInterface $context
     */
    public function validateFamilyAndParentCategory(ExecutionContextInterface $context)
    {
//        if (!$this->family && !$this->parentCategory) {
//            $context->addViolationAt('parentCategory', 'category.missing.family.and.parent.category');
//        } else if ($this->family && $this->parentCategory) {
//            $context->addViolationAt('parentCategory', 'category.exclusion.with.family');
//        }
    }

    /**
     * Add attribute
     *
     * @param Attribute $attribute
     *
     * @return Category
     */
    public function addAttribute(Attribute $attribute)
    {
        $this->attributes->add($attribute);

        return $this;
    }

    /**
     * Remove attribute
     *
     * @param Attribute $attribute
     */
    public function removeAttribute(Attribute $attribute)
    {
        $this->attributes->removeElement($attribute);
    }

    /**
     * Get attributes
     *
     * @return ArrayCollection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Add feature
     *
     * @param Feature $feature
     *
     * @return Category
     */
    public function addFeature(Feature $feature)
    {
        $this->features->add($feature);

        return $this;
    }

    /**
     * Remove feature
     *
     * @param Feature $feature
     */
    public function removeFeature(Feature $feature)
    {
        $this->features->removeElement($feature);
    }

    /**
     * Get features
     *
     * @return ArrayCollection
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * Add product
     *
     * @param Product $product
     *
     * @return Category
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
     * Add brand
     *
     * @param Brand $brand
     *
     * @return Category
     */
    public function addBrand(Brand $brand)
    {
        $this->brands->add($brand);

        return $this;
    }

    /**
     * Remove product
     *
     * @param Brand $brand
     */
    public function removeBrand(Brand $brand)
    {
        $this->brands->removeElement($brand);
    }

    /**
     * Get brands
     *
     * @return ArrayCollection
     */
    public function getBrands()
    {
        return $this->brands;
    }
    
    /**
     * Set order
     *
     * @param integer $order
     *
     * @return Slider
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
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
     * @return string
     */
    public function __toString()
    {
        return $this->name;
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

    
}