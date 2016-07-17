<?php

namespace EcommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CoreBundle\Entity\Timestampable;

/**
 * Rangeable abstract class to define created and updated behaviors
 *
 * @ORM\MappedSuperclass
 */
abstract class Rangeable  extends Timestampable
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="from_date", type="datetime")
     */
    private $from;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="to_date", type="datetime")
     */
    private $to;


    protected $rangeDate;
    /**
     * Get from
     *
     * @return \DateTime
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Get to
     *
     * @return \DateTime
     */
    public function getTo()
    {
        return $this->to;
    }


    /**
     * Set from
     *
     * @param \DateTime $from
     *
     * @return Rangeable
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Set to
     *
     * @param \DateTime $to
     *
     * @return Rangeable
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getRangeDate()
    {
        $from = new \DateTime();
        $to = clone $from;
//        $dateString = $from->format('d/m/Y').' '.$to->format('d/m/Y');
        $dateString = '';
        if($this->from != '' && $this->to != '')
        $dateString = $this->from->format('d/m/Y').' '.$this->to->format('d/m/Y');
        return $dateString;
    }

    /**
     * @param string $publishDateRange
     */
    public function setRangeDate($publishDateRange)
    {
        if($publishDateRange != ''){
            $arr = explode(' ', $publishDateRange);
            $this->from = \DateTime::createFromFormat('d/m/Y', $arr[0]);
            $this->to = \DateTime::createFromFormat('d/m/Y', $arr[1]);
        }
    }
    
}