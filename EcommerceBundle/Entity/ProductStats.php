<?php

namespace EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ProductStats Entity class
 *
 * @ORM\Table(name="product_stats")
 * @ORM\Entity
 */
class ProductStats
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
     * @ORM\Column(name="visits", type="integer", nullable=true)
     */
    private $visits;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="day", type="string", nullable=true)
     */
    private $day;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="stats")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank
     */
    private $product;
    


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
     * Set visits
     *
     * @param integer $visits
     *
     * @return ProductStats
     */
    public function setVisits($visits)
    {
        $this->visits = $visits;

        return $this;
    }

    /**
     * Get visits
     *
     * @return integer 
     */
    public function getVisits()
    {
        return $this->visits;
    }

    /**
     * Set day
     *
     * @param string $day
     *
     * @return ProductStats
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get day
     *
     * @return string 
     */
    public function getDay()
    {
        return $this->day;
    }    

    /**
     * Set product
     *
     * @param Product $product
     *
     * @return TransactionItem
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}