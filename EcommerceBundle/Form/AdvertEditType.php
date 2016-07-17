<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EcommerceBundle\Form\AdvertImageType;
use EcommerceBundle\Form\CreditCardType;

class AdvertEditType extends AbstractType
{
    public $formConfig;
    
    public function __construct($formConfig) {
        $this->formConfig = $formConfig;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('image', new AdvertImageType(), array(
                'error_bubbling' => false,
                'required' => false
            ));
        if(isset($this->formConfig['admin'])){
            $builder->add('active');
        }
        
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\Advert'
        ));
    }

}
