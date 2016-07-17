<?php
namespace EcommerceBundle\Workflow\Payment;

use EcommerceBundle\Workflow\AbstractWorkflow;
use EcommerceBundle\Workflow\WorkflowInterface;
use EcommerceBundle\Workflow\Payment\State\CreateState;

class RelationWorkflow extends AbstractWorkflow implements WorkflowInterface
{
    public function __construct()
    {
        $this->state = new CreateState($this);
    }

    public function methodCreate($owner, $type, $ownerDest, $relationManager)
    {
        return $this->state->methodCreate($owner, $type, $ownerDest, $relationManager);
    }

    public function methodPending($owner, $type, $ownerDest, $relationManager)
    {
        return $this->state->methodPending($owner, $type, $ownerDest, $relationManager);
    }

    public function methodAccept($owner, $type, $ownerDest, $relationManager)
    {
        return $this->state->methodAccept($owner, $type, $ownerDest, $relationManager);
    }

    public function methodDenied($owner, $type, $ownerDest, $relationManager)
    {
        return $this->state->methodDenied($owner, $type, $ownerDest, $relationManager);
    }
}
