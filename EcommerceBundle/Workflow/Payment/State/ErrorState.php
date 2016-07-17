<?php
namespace ContactBundle\Workflow\Payment\State;

use EcommerceBundle\Workflow\Payment\AbstractState;
use EcommerceBundle\Workflow\Payment\StateInterface;
use EcommerceBundle\Workflow\WorkflowInterface;

class ErrorState extends AbstractState implements StateInterface
{
    /**
     * @var StateInterface
     */
    private $previousState;

    public function __construct(WorkflowInterface $workflow, StateInterface $previousState = null)
    {
        parent::__construct($workflow);
        $this->previousState = $previousState;
    }
}
