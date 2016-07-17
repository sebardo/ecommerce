<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use CoreBundle\Form\ImageType;

/**
 * Class CategoryType
 */
class CategoryType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('active', null, array('required' => false
            ))
            ->add('metaTitle')
            ->add('metaDescription')
            ->add('metaTags')
            ->add('parentCategory', 'entity', array(
                'class' => 'EcommerceBundle:Category',
                'required' => false
            ))
            ->add('image', new ImageType(), array(
                'error_bubbling' => false,
                'required' => false
            ))
            ->add('removeImage', 'hidden', array( 'attr' => array(
                'class' => 'remove-image'
                )))
            ->add('url')
            ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\Category',
            //'cascade_validation' => true,
        ));
    }

}
