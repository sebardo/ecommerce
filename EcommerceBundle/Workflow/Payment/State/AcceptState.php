<?php
namespace EcommerceBundle\Workflow\Payment\State;

use EcommerceBundle\Workflow\Payment\AbstractState;
use EcommerceBundle\Workflow\Payment\StateInterface;
use EcommerceBundle\Workflow\State\ErrorState;
use EcommerceBundle\Entity\RelationStore;

class AcceptState extends AbstractState implements StateInterface
{
    public function methodDenied($owner, $type, $ownerDest, $relationManager)
    {
        try {

            $relationStore = $relationManager->setRelation($owner, $type, $ownerDest, RelationStore::STATE_DENIED);

        } catch (\Exception $e) {
            $this->workflow->setState(new ErrorState($this->workflow, $this));

            return $this->workflow;
        }

        $this->workflow->setState(new DeniedState($this->workflow));

        return $this->workflow;
    }
}
