<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EcommerceBundle\Form\AdvertImageType;
use EcommerceBundle\Form\CreditCardType;

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
            ->add('geolocated', 'choice', array(
                'choices'  => array(0 => 'No', 1 => 'Si', 'all' => 'Todo'),
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'empty_data' => null
//                'placeholder' => 'Selecciona el tipo de envío'
            ))
            ->add('rangeDate', 'text')
            ->add('days', 'hidden')
            ->add('image', new AdvertImageType(), array(
                'error_bubbling' => false,
                'required' => false
            ))
            ->add('located')
            ->add('cities', 'hidden')
            ->add('cityAutocomplete', 'text', array(
                'required' => false,
                'attr' => array(
                        'multiple'=> true,
                        'data-url' => "/cities-postalcodes.json",
//                        'name' => "language"
                        )
                    )
                )    
            ->add('codes', 'text', array(
                'attr' => array(
                        'multiple'=> true,
                        'data-url' => "/postalcodes.json",
                        'name' => "language",
                        'autocomplete' => 'off'
                        )
                    )
                )
            ->add('creditCard', new CreditCardType())
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
