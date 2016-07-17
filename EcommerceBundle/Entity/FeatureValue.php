<?php

namespace EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Entity\Image;
use CoreBundle\Entity\Timestampable;

/**
 * FeatureValue Entity class
 *
 * @ORM\Table(name="feature_value")
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\FeatureValueRepository")
 */
class FeatureValue extends Timestampable
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
     * @ORM\OneToOne(targetEntity="CoreBundle\Entity\Image", cascade={"persist", "remove"})
     */
    private $image;

    /**
     * @var Feature
     *
     * @ORM\ManyToOne(targetEntity="Feature", inversedBy="values")
     * @ORM\JoinColumn(name="feature_id", referencedColumnName="id", nullable=false)
     * @Assert\NotBlank
     */
    private $feature;


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
     * @return FeatureValue
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
     * @return FeatureValue
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
     * Set feature
     *
     * @param Feature $feature
     *
     * @return FeatureValue
     */
    public function setFeature(Feature $feature = null)
    {
        $this->feature = $feature;

        return $this;
    }

    /**
     * Get feature
     *
     * @return Feature
     */
    public function getFeature()
    {
        return $this->feature;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->feature->getName().': '.$this->name;
    }
}