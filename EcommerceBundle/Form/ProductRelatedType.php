<?php

namespace EcommerceBundle\Form;

use Doctrine\ORM\EntityRepository;
use EcommerceBundle\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductRelatedType
 */
class ProductRelatedType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('relatedProducts', 'entity', array(
                'class'    => 'EcommerceBundle:Product',
                'multiple' => true,
            ));
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\Product',
        ));
    }

}
