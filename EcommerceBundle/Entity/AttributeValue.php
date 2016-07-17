<?php

namespace EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Entity\Timestampable;
use CoreBundle\Entity\Image;

/**
 * AttributeValue Entity class
 *
 * @ORM\Table(name="attribute_value")
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\AttributeValueRepository")
 */
class AttributeValue extends Timestampable
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
     * @var Attribute
     *
     * @ORM\ManyToOne(targetEntity="Attribute", inversedBy="values", fetch="EAGER")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank
     */
    private $attribute;

    /**
     * @var Image
     *
     * @ORM\OneToOne(targetEntity="CoreBundle\Entity\Image", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private $image;


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
     * @return AttributeValue
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set attribute
     *
     * @param Attribute $attribute
     *
     * @return AttributeValue
     */
    public function setAttribute(Attribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return Attribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set image
     *
     * @param Image $image
     *
     * @return AttributeValue
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
     * @return string
     */
    public function __toString()
    {
        return $this->attribute->getName().': '.$this->name;
    }
}