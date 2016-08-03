<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class PlanType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('paypalId')
            ->add('name')
            ->add('description')
            ->add('setupAmount')
            ->add('frequency' , ChoiceType::class, array(
                'choices'  => array(
                    'DAY' => 'DAY', 
                    'WEEK' => 'WEEK', 
                    'MONTH' => 'MONTH', 
                    'YEAR' => 'YEAR'
                ),
                'choices_as_values' => true
            ))
            ->add('frequencyInterval', IntegerType::class)
            ->add('cycles', IntegerType::class)
            ->add('amount')
            ->add('trialFrequency' , ChoiceType::class, array(
                'required' => false,
                'choices'  => array(
                    'DAY' => 'DAY', 
                    'WEEK' => 'WEEK', 
                    'MONTH' => 'MONTH', 
                    'YEAR' => 'YEAR'
                ),
                'choices_as_values' => true
            ))
            ->add('trialFrequencyInterval', IntegerType::class, array('required' => false))
            ->add('trialCycles', IntegerType::class, array('required' => false))
            ->add('trialAmount', null, array('required' => false))
            ->add('visible', null, array('required' => false))
            ->add('active', null, array('required' => false))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\Plan'
        ));
    }

}
