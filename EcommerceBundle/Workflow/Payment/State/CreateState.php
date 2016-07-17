<?php
namespace EcommerceBundle\Workflow\Payment\State;

use EcommerceBundle\Workflow\Payment\AbstractState;
use EcommerceBundle\Workflow\Payment\StateInterface;

class CreateState extends AbstractState implements StateInterface
{
    public function methodCreate($owner, $type, $ownerDest, $relationManager)
    {
        try {

            //check if exist and state
            $relationStore = $relationManager->setRelation($owner, $type, $ownerDest);

        } catch (\ExceptionBase $e) {
            $this->workflow->setState(new ErrorState($this->workflow, $this));

            return $this->workflow;
        }

        $class = $relationStore->getStatus();
        $instance = new $class($this->workflow);

        $this->workflow->setState($instance);

        return $this->workflow;
    }
}
