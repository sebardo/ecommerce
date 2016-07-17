<?php

namespace EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CoreBundle\Entity\Timestampable;

/**
 * Cart Entity class
 *
 * 
 * @ORM\Table(name="listener_log")
 * @ORM\Entity(repositoryClass="EcommerceBundle\Entity\Repository\ListenerLogRepository")
 */
class ListenerLog extends Timestampable
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
     * Type of respose(paypal,redsys, etc)
     *
     * @var string
     * 
     * @ORM\Column(name="type", type="string")
     */
    protected $type;
    
    /**
     * Total items count.
     *
     * @var integer
     * 
     * @ORM\Column(name="input", type="text")
     */
    protected $input;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="valid", type="boolean")
     */
    private $valid;
    
     /**
     * Constructor
     */
    public function __construct()
    {
        $this->valid = false;
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
    
    /**
     * {@inheritdoc}
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * {@inheritdoc}
     */
    public function setInput($input)
    {
        $this->input = $input;

        return $this;
    }
    
    /**
     * Is valid?
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }
    
    /**
     * Set valid
     *
     * @param boolean $valid
     *
     * @return ListenerLog
     */
    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }
}
