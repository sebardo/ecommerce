<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use EcommerceBundle\Form\CreditCardType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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
            ->add('description', TextareaType::class)
            ->add('plan', EntityType::class, array(
                'class' => 'EcommerceBundle:Plan',
                'choice_label' => 'name',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->where('p.active = true')
                        ->orderBy('p.name', 'ASC');
                },
            ))
            ->add('paymentMethod', ChoiceType::class, array(
                'choices' => array(
                    //'paypal' => 'Paypal',
                    'credit_card' => 'Tarjeta de crédito'
                    
                )
            ))
            ->add('creditCard', CreditCardType::class)
             
      
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
