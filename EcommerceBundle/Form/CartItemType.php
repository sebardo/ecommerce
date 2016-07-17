<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartItemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', 'integer', array('attr' => array('min' => 1, 'class' => 'quantity')))
            ->add('shippingCost', 'hidden')
            ->add('storePickup', 'choice', array(
                'choices'  => array(1 => 'Recoger en tienda', 0 => 'Envio On-line'),
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'empty_data' => null
//                'placeholder' => 'Selecciona el tipo de envío'
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'data_class' => 'EcommerceBundle\Entity\CartItem',
            ))
        ;
    }

}
