<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('frequency' , 'choice', array(
                'choices'  => array(
                    'DAY' => 'DAY', 
                    'WEEK' => 'WEEK', 
                    'MONTH' => 'MONTH', 
                    'YEAR' => 'YEAR'
                ),
            ))
            ->add('frequencyInterval', 'integer')
            ->add('cycles', 'integer')
            ->add('amount')
            ->add('trialFrequency' , 'choice', array(
                'required' => false,
                'choices'  => array(
                    'DAY' => 'DAY', 
                    'WEEK' => 'WEEK', 
                    'MONTH' => 'MONTH', 
                    'YEAR' => 'YEAR'
                ),
            ))
            ->add('trialFrequencyInterval', 'integer', array('required' => false))
            ->add('trialCycles', 'integer', array('required' => false))
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
