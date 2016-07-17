<?php
namespace EcommerceBundle\Workflow;

use EcommerceBundle\Workflow\Payment\AbstractState;

abstract class AbstractWorkflow
{
    /**
     * @var StateInterface
     */
    protected $state;

    public function setState(AbstractState $state)
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }
}
