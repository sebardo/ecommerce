<?php

namespace EcommerceBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use CoreBundle\Form\ImageType;

/**
 * Class FeatureType
 */
class FeatureType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('category', 'entity', array(
                'class' => 'EcommerceBundle:Category',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c');
//                        ->where('c.family IS NOT NULL');
                },
                'required' => false
            ))
//            ->add('order', 'number', array(
//                'required' => false
//            ))
//            ->add('filtrable', 'checkbox', array(
//                'required' => false
//            ))
//            ->add('rangeable', 'checkbox', array(
//                'required' => false
//            ))
            ->add('image', new ImageType(), array(
                'error_bubbling' => false,
                'required' => false
            ));
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\Feature',
        ));
    }

}
