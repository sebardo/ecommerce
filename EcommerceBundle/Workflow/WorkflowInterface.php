<?php
namespace EcommerceBundle\Workflow;

use EcommerceBundle\Workflow\Relation\AbstractState;

interface WorkflowInterface
{
    public function setState(AbstractState $state);
}
