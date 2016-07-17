<?php
namespace EcommerceBundle\Workflow\Payment;

use EcommerceBundle\Workflow\WorkflowInterface;

abstract class AbstractState implements StateInterface
{
    /**
     * @var WorkflowInterface
     */
    protected $workflow;

    public function __construct(WorkflowInterface $workflow)
    {
        $this->workflow = $workflow;
    }

    public function methodCreate($owner, $type, $ownerDest, $relationManager)
    {
        throw new \Exception('You should not call that method in that state');
    }

    public function methodAccept($owner, $type, $ownerDest, $relationManager)
    {
        throw new \Exception('You should not call that method in that state');
    }

    public function methodDenied($owner, $type, $ownerDest, $relationManager)
    {
        throw new \Exception('You should not call that method in that state');
    }
}
