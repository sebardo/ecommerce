<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use CoreBundle\Form\ImageType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class FeatureValueType
 */
class FeatureValueType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('image', ImageType::class, array(
                'required' => false
            ))
            ->add('removeImage', HiddenType::class, array( 'attr' => array(
            'class' => 'remove-image'
            )));
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\FeatureValue',
        ));
    }

}
