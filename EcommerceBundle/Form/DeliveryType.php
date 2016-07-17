<?php

namespace EcommerceBundle\Form;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use EcommerceBundle\Entity\Delivery;
use EcommerceBundle\Entity\Actor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class DeliveryType
 */
class DeliveryType extends AbstractType
{
    private $securityContext;
    private $em;
    private $session;

    /**
     * @param SecurityContext $securityContext
     * @param EntityManager   $em
     * @param Session         $session
     */
    public function __construct(SecurityContext $securityContext, EntityManager $em, Session $session)
    {
        $this->securityContext = $securityContext;
        $this->em = $em;
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User $user */
        $user = $this->securityContext->getToken()->getUser();
        if (!$user) {
            throw new \LogicException('The DeliveryType cannot be used without an authenticated user!');
        }

        $numDeliveryAddresses = $this->em->getRepository('EcommerceBundle:Address')->countTotal($user->getId(), false);

        // initialize delivery addresses options
        $selectDelivery['same'] = 'account.address.select.same';
        if ($numDeliveryAddresses > 0) {
            $selectDelivery['existing'] = 'account.address.select.existing';
        }
        $selectDelivery['new'] = 'account.address.select.new';

        $builder
            ->add('fullName', null, array(
                'required' => false
            ))
            ->add('dni', null, array(
                'required' => false
            ))
            ->add('address', null, array(
                'required' => false
            ))
            ->add('city', 'text', array(
                'required' => false
            ))
            ->add('state', 'entity', array(
                'class' => 'CoreBundle:State',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c');
                },
                'required' => false,
                'placeholder' => 'Selecciona tu provincia',
                'empty_data'  => null
            ))
            
            ->add('postalCode', null, array(
                'required' => false
            ))
            ->add('phone', null, array(
                'required' => false
            ))
            ->add('phone2', null, array(
                'required' => false
            ))
            ->add('preferredSchedule', 'choice', array(
                'choices'  => Delivery::getSchedules(),
                'required' => false
            ))

            ->add('deliveryDni', 'text', array(
                'required' => false
            ))
            ->add('deliveryAddress', 'text', array(
                'required' => false
            ))
            ->add('deliveryCity', 'text', array(
                'required' => false
            ))
            ->add('deliveryState', 'entity', array(
                    'class' => 'CoreBundle:State',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c');
                    },
                    'required' => false,
                    'placeholder' => 'Selecciona tu provincia',
                    'empty_data'  => null
                ))
            ->add('deliveryPostalCode', 'text', array(
                'required' => false
            ))
            ->add('deliveryPhone', 'text', array(
                'required' => false
            ))
            ->add('deliveryPhone2', 'text', array(
                'required' => false
            ))
            ->add('deliveryPreferredSchedule', 'choice', array(
                'choices'  => Delivery::getSchedules(),
                'required' => false
            ))

//            ->add('carrier', 'entity', array(
//                'class' => 'ModelBundle:Carrier',
//                'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('c')
//                        ->orderBy('c.expenses', 'asc');
//                }
//            ))
            ->add('notes', 'textarea', array(
                'required' => false
            ));

            $factory = $builder->getFormFactory();

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($user, $numDeliveryAddresses, $factory) {
            $form = $event->getForm();
 

            // delivery addresses
            if ($numDeliveryAddresses > 0) {
                $existingDeliveryAddress = null;

//                if ($this->session->has('select-delivery') && 'existing' === $this->session->get('select-delivery')) {
//                    $existingDeliveryAddress = $this->session->get('existing-delivery-address');
//                }

                $deliveryAddressData = !is_null($existingDeliveryAddress) ?
                    $this->em->getReference('EcommerceBundle:Address', $existingDeliveryAddress) :
                    null;

                $formOptions = array(
                    'class'         => 'EcommerceBundle\Entity\Address',
                    'multiple'      => false,
                    'expanded'      => false,
                    'mapped'        => false,
                    'required'      => false,
                    'auto_initialize' => false,
                    'data'          => $deliveryAddressData,
                    'query_builder' => function(EntityRepository $er) use ($user) {
                        return $er->createQueryBuilder('a')
                            ->where('a.actor = :user')
                            ->andWhere('a.forBilling = false')
                            ->setParameter('user', $user);
                    }
                );

                $form->add($factory->createNamed('existingDeliveryAddress', 'entity', null, $formOptions));
            }
        });

        $builder->add('selectDelivery', 'choice', array(
            'choices'  => $selectDelivery,
            'multiple' => false,
            'expanded' => true,
            'required' => true,
            'label'    => false,
            'mapped'   => false,
            'data'     => $this->session->has('select-delivery') ? $this->session->get('select-delivery') : 'same'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\Delivery',
        ));
    }
}
