<?php
namespace EcommerceBundle\Workflow\Payment;

interface StateInterface
{
    public function methodCreate($owner, $type, $ownerDest, $relationManager);
    public function methodAccept($owner, $type, $ownerDest, $relationManager);
    public function methodDenied($owner, $type, $ownerDest, $relationManager);
}
