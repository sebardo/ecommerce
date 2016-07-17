<?php

namespace EcommerceBundle\Form;

use Doctrine\ORM\EntityRepository;
use EcommerceBundle\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductAttributesType
 */
class ProductAttributesType extends AbstractType
{
    private $category;


    /**
     * @param Category $category
     */
    public function __construct($category)
    {
        $this->category = $category;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $category = $this->category;

        $builder
            ->add('attributeValues', 'entity', array(
                'class'         => 'EcommerceBundle:AttributeValue',
                'group_by'      => 'attribute.name',
                'multiple'      => true,
                'query_builder' => function(EntityRepository $er) use ($category) {
                    return $er->createQueryBuilder('av')
                        ->join('av.attribute', 'a')
                        ->where('a.category = :category')
                        ->setParameter('category', $category)
                        ;
                }
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
