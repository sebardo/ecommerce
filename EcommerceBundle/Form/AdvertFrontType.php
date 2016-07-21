<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EcommerceBundle\Form\AdvertImageType;
use EcommerceBundle\Form\CreditCardType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class AdvertFrontType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('brand', null, array(
                'required' => false,
            ))

            ->add('rangeDate', TextType::class)
            ->add('days', HiddenType::class)
            ->add('image', AdvertImageType::class, array(
                'required' => false
            ))
            ->add('located')
            ->add('creditCard', CreditCardType::class)
        ;
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
