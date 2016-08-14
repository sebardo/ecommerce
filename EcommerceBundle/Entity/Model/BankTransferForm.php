<?php
namespace EcommerceBundle\Entity\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * BankTransferForm Model
**/
class BankTransferForm
{

    protected $validator;
    
    protected $bankTransfer;
       
    public function __construct($validator) {
        $this->validator = $validator;
    }
    
    public function getBankTransfer() {
        return $this->bankTransfer;
    }
    
    public function setBankTransfer($bankTransfer) {
        $this->bankTransfer = $bankTransfer;
    }
    
}
