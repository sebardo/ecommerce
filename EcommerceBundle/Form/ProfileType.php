<?php

namespace EcommerceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use EcommerceBundle\Entity\Actor;
use Symfony\Component\OptionsResolver\OptionsResolver;
use CoreBundle\Form\EmailType;

/**
 * {@inheritDoc}
 */
class ProfileType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder
            ->add('email', EmailType::class, array('label' => 'form.email'))
            ->add('name')
            ->add('lastname')
            ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\Actor'
        ));
    }

}