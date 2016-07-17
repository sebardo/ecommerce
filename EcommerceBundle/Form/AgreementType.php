<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use EcommerceBundle\Form\CreditCardType;

class AgreementType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name')
            ->add('description', 'textarea')
            ->add('plan', 'entity', array(
                'class' => 'EcommerceBundle:Plan',
                'property' => 'name',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.active = true')
                        ->orderBy('p.name', 'ASC');
                },
            ))
            ->add('paymentMethod', 'choice', array(
                'choices' => array(
                    //'paypal' => 'Paypal',
                    'credit_card' => 'Tarjeta de crédito'
                    
                )
            ))
            ->add('creditCard', new CreditCardType())
             
      
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\Agreement'
        ));
    }

}
