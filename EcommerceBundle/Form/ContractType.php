<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use EcommerceBundle\Form\AgreementType;

class ContractType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('actor', 'entity', array(
                'class' => 'CoreBundle:Actor',
                'property' => 'name',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
            ))
            ->add('url', 'text', array(
                'attr' => array(
                    'placeholder' => 'http://'
                )
            ))
            ->add('created', 'datetime', array(
                    'label' => 'Fecha inicio',
                    'format' => 'dd/MM/yyyy',
                    'widget' => 'single_text',
                    'required' => false
                )
            )
            ->add('finished', 'datetime', array(
                    'label' => 'Fecha límite',
                    'format' => 'dd/MM/yyyy',
                    'widget' => 'single_text',
                    'required' => false
                )
            )
            
           ->add('agreement', new AgreementType())
                      
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
