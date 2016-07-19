<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use EcommerceBundle\Form\AgreementType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
Use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ContractType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('actor', EntityType::class, array(
                'class' => 'CoreBundle:Actor',
                'choice_label' => 'name',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
            ))
            ->add('url', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'http://'
                )
            ))
            ->add('created', DateTimeType::class, array(
                    'label' => 'Fecha inicio',
                    'format' => 'dd/MM/yyyy',
                    'widget' => 'single_text',
                    'required' => false
                )
            )
            ->add('finished', DateTimeType::class, array(
                    'label' => 'Fecha límite',
                    'format' => 'dd/MM/yyyy',
                    'widget' => 'single_text',
                    'required' => false
                )
            )
            
           ->add('agreement', AgreementType::class)
                      
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\Contract'
        ));
    }

}
