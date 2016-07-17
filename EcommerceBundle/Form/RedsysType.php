<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class RedsysType
 */
class RedsysType extends AbstractType
{
    private $formConfig;


    /**
     * @param SecurityContext $securityContext
     */
    public function __construct($formConfig)
    {
        $this->formConfig = $formConfig;
    }
    
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            ->add('Ds_Merchant_MerchantData', 'hidden', array('data' => $this->formConfig['data']))
            ->add('Ds_Merchant_MerchantName', 'hidden', array('data' => $this->formConfig['name']))
            ->add('Ds_Merchant_ProductDescription', 'hidden', array('data' => $this->formConfig['product']))
            ->add('Ds_Merchant_Titular', 'hidden', array('data' => $this->formConfig['titular']))
            ->add('Ds_Merchant_Amount', 'hidden', array('data' => $this->formConfig['amount']))
            ->add('Ds_Merchant_Currency', 'hidden', array('data' => $this->formConfig['currency']))
            ->add('Ds_Merchant_Order', 'hidden', array('data' => $this->formConfig['order']))
            ->add('Ds_Merchant_MerchantCode', 'hidden', array('data' => $this->formConfig['code']))
            ->add('Ds_Merchant_Terminal', 'hidden', array('data' => $this->formConfig['terminal']))
            ->add('Ds_Merchant_TransactionType', 'hidden', array('data' => $this->formConfig['transaction_type']))
            ->add('Ds_Merchant_MerchantURL', 'hidden', array('data' => $this->formConfig['bank_response_url']))
            ->add('Ds_Merchant_UrlOK', 'hidden', array('data' => $this->formConfig['return_url']))
            ->add('Ds_Merchant_UrlKO', 'hidden', array('data' => $this->formConfig['cancel_url']))
            ->add('Ds_Merchant_MerchantSignature', 'hidden', array('data' => $this->formConfig['signature']))
            ->add('Ds_Merchant_ConsumerLanguage', 'hidden', array('data' => $this->formConfig['consumer_language']))
            ;
    }

}
