<?php

namespace EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Cart Entity class
 *
 * 
 * @ORM\Table(name="tax")
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\TaxRepository")
 */
class Tax 
{

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Total items count.
     *
     * @var integer
     * 
     * @ORM\Column(name="tax", type="integer")
     */
    protected $tax;
    
    
   /**
     * Total items count.
     *
     * @var integer
     * 
     * @ORM\Column(name="type", type="string")
     */
    protected $type='percent';

    public function __toString() {
        return $this->tax .' %';
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * {@inheritdoc}
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

     /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
    
}
