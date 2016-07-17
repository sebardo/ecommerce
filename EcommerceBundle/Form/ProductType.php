<?php

namespace EcommerceBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductType
 */
class ProductType extends AbstractType
{
    public $formConfig;
    
    public function __construct($formConfig) {
        $this->formConfig = $formConfig;
    }
    
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $actorArray = array (
                    'class' => 'CoreBundle:Actor',
                    'property' => 'name',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('o')
                            ->orderBy('o.name', 'ASC');
                    },
                    'required' => false     
                );
                    
        $categoryArray = array (
                    'class' => 'EcommerceBundle:Category',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                               ->where('c.active = true')
                               ->orderBy('c.order', 'ASC');
                    },
                    'required' => false
                );
                    
        if(isset($this->formConfig['actor'])){
            $actor = $this->formConfig['actor'];
            $actorArray = array_merge($actorArray, array (
                    'query_builder' => function(EntityRepository $er)use($actor) {
                        return $er->createQueryBuilder('o')
                            ->where('o.id = :actor')
                            ->setParameter('actor', $actor)
                            ->orderBy('o.name', 'ASC');
                    },
                    'data' => $actor
                ));
            $categoryArray = array_merge($categoryArray, array (
                    'query_builder' => function(EntityRepository $er)use($actor) {
                        return $er->createQueryBuilder('c')
                            ->where('c.active = true')
                            ->orWhere('c.actor = :actor')
                            ->setParameter('actor', $actor)
                            ->orderBy('c.name', 'ASC');
                    }
                ));
        }else{
            $builder->add('active', null, array(
                'required' => false
            ));
            $builder->add('freeTransport', null, array(
                'required' => false
            ));
        }
        $builder
            ->add('name')
            ->add('description', 'textarea')
            ->add('slug', 'text', array('required' => false))
            ->add('initPrice', 'text', array('required' => false))
            ->add('price', 'text')    
            ->add('priceType', 'choice', array(
                'choices'  => array('0' => 'Euro "€"', '1' => 'Porcentaje "%"'),
                'required' => true
            ))
            ->add('discount', 'text', array('required' => false))    
            ->add('discountType', 'choice', array(
                'choices'  => array('0' => 'Porcentaje "%"', '1' => 'Fijo "€"'),
                'required' => false
            ))
            ->add('stock', null, array(
                'required' => true
            ))
            ->add('weight', null, array(
                'required' => false
            ))
            ->add('outlet', null, array(
                'required' => false
            ))
            ->add('public', null, array(
                'required' => false
            ))
            ->add('storePickup', null, array(
                'required' => false
            ))
            ->add('publishDateRange', 'text')
            ->add('metaTitle', null, array(
                'required' => false
            ))
            ->add('metaDescription', null, array(
                'required' => false
            ))
            ->add('metaTags')
            
            ->add('actor', 'entity', $actorArray)
            ->add('category', 'entity', $categoryArray)
            ->add('brand', 'entity', array(
                'class' => 'EcommerceBundle:Brand',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('b')
                             ->orderBy('b.name');
                },
                'required' => false
            ))
            ->add('model', 'entity', array(
                'class' => 'EcommerceBundle:BrandModel',
                'required' => false
            ))
            ->add('available', null, array(
                'required' => false
            ))
            ->add('highlighted', null, array(
                    'required' => false
                ))
            
            ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\Product'
        ));
    }

}
