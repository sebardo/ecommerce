<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EcommerceBundle\Form\AdvertImageType;
use EcommerceBundle\Form\CreditCardType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class AdvertType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
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

        }

        $builder
            ->add('title')
            ->add('description')
            ->add('rangeDate', TextType::class)
            ->add('days', HiddenType::class)
            ->add('image', AdvertImageType::class, array(
                'error_bubbling' => false,
                'required' => false
            ))
            ->add('actor', EntityType::class, $actorArray)
            ->add('located')
            ->add('creditCard', CreditCardType::class);
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\Advert',
            'actor' => null
        ));
    }

}
