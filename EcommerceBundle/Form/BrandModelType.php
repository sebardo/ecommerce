<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use CoreBundle\Form\ImageType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Class BrandModelType
 */
class BrandModelType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('available', null, array(
                'required' => false
            ))
            ->add('image', ImageType::class, array(
                'required' => false
            ))
            ->add('removeImage', HiddenType::class, array( 'attr' => array(
                'class' => 'remove-image'
                )))
            ->add('brand', EntityType::class, array(
                'class' => 'EcommerceBundle:Brand',
                'required' => true
            ))
            ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\BrandModel',
        ));
    }

}
