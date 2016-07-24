<?php

namespace EcommerceBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EcommerceBundle\Entity\Product;

/**
 * Class ProductType
 */
class ProductType extends AbstractType
{

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $actorArray = array (
                    'class' => 'CoreBundle:Actor',
                    'choice_label' => 'name',
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
                    
        if(isset($options['actor'])){
            $actor = $options['actor'];
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
            ->add('description', TextareaType::class)
            ->add('slug', TextType::class, array('required' => false))
            ->add('initPrice', TextType::class, array('required' => false))
            ->add('price', TextType::class)    
            ->add('priceType', ChoiceType::class, array(
                'choices'  => array('Euro "€"'=> Product::PRICE_TYPE_FIXED, 'Porcentaje "%"' => Product::PRICE_TYPE_PERCENT),
                'required' => true,
                'choices_as_values' => true,
            ))
            ->add('discount', TextType::class, array('required' => false))    
            ->add('discountType', ChoiceType::class, array(
                'choices'  => array('Fijo "€"'=> Product::PRICE_TYPE_FIXED, 'Porcentaje "%"' => Product::PRICE_TYPE_PERCENT),
                'required' => false,
                'choices_as_values' => true,
            ))
            ->add('stock', null, array(
                'required' => true
            ))
            ->add('weight', null, array(
                'required' => false
            ))
            ->add('public', null, array(
                'required' => false
            ))
            ->add('storePickup', null, array(
                'required' => false
            ))
            ->add('publishDateRange', TextType::class)
            ->add('metaTitle', null, array(
                'required' => false
            ))
            ->add('metaDescription', null, array(
                'required' => false
            ))
            ->add('metaTags')
            
            ->add('actor', EntityType::class, $actorArray)
            ->add('category', EntityType::class, $categoryArray)
            ->add('brand', EntityType::class, array(
                'class' => 'EcommerceBundle:Brand',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('b')
                             ->orderBy('b.name');
                },
                'required' => false
            ))
            ->add('model', EntityType::class, array(
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
