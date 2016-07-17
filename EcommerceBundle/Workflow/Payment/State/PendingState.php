<?php
namespace EcommerceBundle\Workflow\Payment\State;

use EcommerceBundle\Workflow\Payment\AbstractState;
use EcommerceBundle\Workflow\Payment\StateInterface;
use EcommerceBundle\Entity\RelationStore;

class PendingState extends AbstractState implements StateInterface
{
    public function methodAccept($owner, $type, $ownerDest, $relationManager)
    {
        try {

             $relationStore = $relationManager->setRelation($owner, $type, $ownerDest, RelationStore::STATE_ACCEPT);

        } catch (\Exception $e) {
            $this->workflow->setState(new ErrorState($this->workflow, $this));

            return $this->workflow;
        }

        $this->workflow->setState(new AcceptState($this->workflow));

        return $this->workflow;
    }

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
