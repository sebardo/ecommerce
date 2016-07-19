<?php
namespace EcommerceBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use EcommerceBundle\Entity\Plan;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class CreditCardType extends AbstractType
{
    protected $formConfig;
    
    
    public function __construct($formConfig=null) {
        $this->formConfig = $formConfig;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $planConfig = array(
                'class' => 'EcommerceBundle:Plan',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                            ->where('p.visible = true')
                            ->andWhere('p.active = true')
                            ;
                },
                'required' => true,
                'placeholder' => 'Selecciona tu plan',
                'empty_data'  => null
            );
        if(isset($this->formConfig['plan'])){
            if($this->formConfig['plan'] instanceof Plan){
                unset($planConfig['placeholder']);
                $planConfig['query_builder'] =  function(EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                            ->where('p.id = :id')
                            ->setParameter(':id', $this->formConfig['plan']->getId())
                            ;
                };
            }
            $builder->add('plan', EntityType::class, $planConfig);
        }
        $builder->add('firstname', TextType::class, array('label' => 'Nombre'));
        $builder->add('lastname', TextType::class, array('label' => 'Apellidos'));
        $builder->add('cardType',
                      ChoiceType::class,
                      array('label' => 'Tipo',
                            'choices' => array(
                                'visa' => 'visa', 
                                'mastercard' => 'mastercard', 
                                'discover' => 'discover', 
                                'amex' => 'amex'),
                           )
                     );
        $builder->add('cardNo', TextType::class, array('label' => 'Número', 'max_length' => 16, 'data' => '4548812049400004'));
        $builder->add('expirationDate',
                       DateType::class,
                       array(
                            'label' => 'Fecha de vencimiento',
                            'empty_value' => array('year' => 'Año',
                                                  'month' => 'Mes',
                                                    'day' => false),
                            'years' => range(date("Y"), date("Y") + 10),
                           'days'            => array(1),
                            'format' => 'dd MM yyyy')
                      );

        $builder->add('CVV', TextType::class, array('max_length' => 4, 'label' => 'CVV'));
        $builder->add('ts', HiddenType::class);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return array(
                'data_class' => 'EcommerceBundle\Entity\CreditCardForm',
        );
    }

}
