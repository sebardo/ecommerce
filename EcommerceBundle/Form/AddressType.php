<?php

namespace EcommerceBundle\Form;

use EcommerceBundle\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class AddressType
 */
class AddressType extends AbstractType
{
    private $securityContext;


    /**
     * @param SecurityContext $securityContext
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dni')
            ->add('address')
            ->add('city')
            ->add('state', EntityType::class, array(
                    'class' => 'CoreBundle:State',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c');
                    },
                    'required' => false,
                    'placeholder' => 'Selecciona tu provincia',
                    'empty_data'  => null
                ))
            ->add('postalCode')
            ->add('phone')
            ->add('phone2')
            ->add('preferredSchedule', ChoiceType::class, array(
                'choices'  => Address::getSchedules(),
                'required' => false,
                'choices_as_values' => true,
            ));

        $user = $this->securityContext->getToken()->getUser();
        if (!$user) {
            throw new \LogicException(
                'The AddressFormType cannot be used without an authenticated user!'
            );
        }

        $factory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($user, $factory) {
            $form = $event->getForm();

            // if user is a business, add the contact person field
//            if ($user::BUSINESS == $user->getAccountType()) {
//                $formOptions = array(
//                    'required' => false
//                );
//
//                $form->add($factory->createNamed('contactPerson', 'text', null, $formOptions));
//            }
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\Address',
        ));
    }
}
