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
    public $formConfig;
    
    public function __construct($formConfig) {
        $this->formConfig = $formConfig;
    }
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
                  
       
            
        $builder
            ->add('title')
            ->add('description')
            ->add('geolocated', ChoiceType::class, array(
                'choices'  => array(0 => 'No', 1 => 'Si', 'all' => 'Todo'),
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'empty_data' => null
//                'placeholder' => 'Selecciona el tipo de envío'
            ))
            ->add('rangeDate', TextType::class)
            ->add('days', HiddenType::class)
            ->add('image', new AdvertImageType(), array(
                'error_bubbling' => false,
                'required' => false
            ));
                
            if(isset($this->formConfig['actor'])){
                $actor = $this->formConfig['actor'];
                $actorArray = array_merge($actorArray, array (
                    'query_builder' => function(EntityRepository $er)use($actor) {
                        return $er->createQueryBuilder('o')
                            ->where('o.id = :actor')
                            ->setParameter('actor', $actor)
                            ->orderBy('o.name', 'ASC');
                    },
                    'data' => $actor
                ));
                $builder->add('actor', EntityType::class, $actorArray);
            }else{
                $builder->add('actor', EntityType::class, $actorArray);
                $builder->add('brand');
            }
        
            
            
            $builder->add('located')
            ->add('cities', 'hidden')
            ->add('cityAutocomplete', TextType::class, array(
                'required' => false,
                'attr' => array(
                        'multiple'=> true,
                        'data-url' => "/cities-postalcodes.json",
                        )
                    )
                )    
            ->add('codes', 'text', array(
                'attr' => array(
                        'multiple'=> true,
                        'data-url' => "/postalcodes.json",
                        'name' => "language"
                        )
                    )
                )
            ->add('creditCard', CreditCardType::class)
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EcommerceBundle\Entity\Advert'
        ));
    }

}
