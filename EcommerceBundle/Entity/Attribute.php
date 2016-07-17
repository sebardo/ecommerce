<?php

namespace EcommerceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Entity\Timestampable;
use CoreBundle\Entity\Image;

/**
 * Attribute Entity class
 *
 * @ORM\Table(name="attribute")
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\AttributeRepository")
 */
class Attribute extends Timestampable
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
     * @var Image
     *
     * @ORM\OneToOne(targetEntity="CoreBundle\Entity\Image", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private $image;

    /**
     * @var integer
     *
     * @ORM\Column(name="_order", type="integer", nullable=true)
     */
    private $order;

    /**
     * @var boolean
     *
     * @ORM\Column(name="filtrable", type="boolean", nullable=true)
     */
    private $filtrable;

    /**
     * @var boolean
     *
     * @ORM\Column(name="rangeable", type="boolean", nullable=true)
     */
    private $rangeable;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AttributeValue", mappedBy="attribute", cascade={"persist", "remove"})
     */
    private $values;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="attributes")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank
     */
    private $category;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
    }

    public function __toString() {
        return $this->name;
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
     * @return Attribute
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set image
     *
     * @param Image $image
     *
     * @return Attribute
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
     * Is filtrable?
     *
     * @return boolean
     */
    public function isFiltrable()
    {
        return $this->filtrable;
    }

    /**
     * Set filtrable
     *
     * @param boolean $filtrable
     *
     * @return Attribute
     */
    public function setFiltrable($filtrable)
    {
        $this->filtrable = $filtrable;

        return $this;
    }

    /**
     * Toggle filtrable
     *
     * @return Attribute
     */
    public function toggleFiltrable()
    {
        $this->filtrable = !$this->filtrable;

        return $this;
    }

    /**
     * Is rangeable?
     *
     * @return boolean
     */
    public function isRangeable()
    {
        return $this->rangeable;
    }

    /**
     * Set rangeable
     *
     * @param boolean $rangeable
     *
     * @return Attribute
     */
    public function setRangeable($rangeable)
    {
        $this->rangeable = $rangeable;

        return $this;
    }

    /**
     * Add value
     *
     * @param AttributeValue $value
     *
     * @return Attribute
     */
    public function addValue(AttributeValue $value)
    {
        $value->setAttribute($this);
        $this->values->add($value);

        return $this;
    }

    /**
     * Remove value
     *
     * @param AttributeValue $value
     */
    public function removeAttribute(AttributeValue $value)
    {
        $this->values->removeElement($value);
    }

    /**
     * Get values
     *
     * @return ArrayCollection
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Get minimum value
     *
     * @return AttributeValue
     */
    public function getMinValue()
    {
        $values = $this->values->toArray();

        uasort($values, $this->cmpValues('getName'));

        return reset($values);
    }

    /**
     * Get maximum value
     *
     * @return AttributeValue
     */
    public function getMaxValue()
    {
        $values = $this->values->toArray();

        uasort($values, $this->cmpValues('getName'));

        return end($values);
    }

    /**
     * Set category
     *
     * @param Category $category
     *
     * @return Attribute
     */
    public function setCategory(Category $category = null)
    {
        $this->category = $category;

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
     * Compares two values
     *
     * @param string $methodName
     *
     * @return callable
     */
    private function cmpValues($methodName)
    {
        return function ($value1, $value2) use ($methodName) {
            return strnatcmp($value1->{$methodName}(), $value2->{$methodName}());
        };
    }
}