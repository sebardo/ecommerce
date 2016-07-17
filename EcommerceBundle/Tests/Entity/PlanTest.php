<?php

namespace EcommerceBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use EcommerceBundle\Entity\Plan;
use DateTime;
use EcommerceBundle\Entity\Agreement;
use EcommerceBundle\Entity\Contract;
use CoreBundle\Entity\Actor;


/**
 * @class  PlanTest
 * @brief Test the  ReportBase entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Entity/PlanTest.php
 * @endcode
 */
class PlanTest  extends WebTestCase
{

    protected function getContainer()
    {
        return static::$kernel->getContainer();
    }
    
    /**
     * @code
     * phpunit -v --filter testCreatePlan -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Entity/PlanTest.php
     * @endcode
     * 
     */
    public function testCreatePlan()
    {
        $client = static::createClient();
        
        $plan = new Plan();
        $plan->setName('Trial Plan '.uniqid());
        $plan->setDescription('Monthly plan for getting something.');
        $plan->setFrequency('MONTH');
        $plan->setFrequencyInterval('1');
        $plan->setCycles('11');
        $plan->setAmount('19.99');
        $plan->setTrialFrequency('MONTH');
        $plan->setTrialFrequencyInterval('1');
        $plan->setTrialCycles('1');
        $plan->setTrialAmount('0');
        
        //error
        $errors = $client->getContainer()->get('validator')->validate($plan);
        $this->assertEquals(count($errors), 0);
        
        //create
        $checkoutManager = $client->getContainer()->get('checkout_manager');
        $payPalPlan = $checkoutManager->createPaypalPlan($plan);
        $this->assertEquals($payPalPlan['state'], 'CREATED');
        
        //active and get
        $payPalPlan = $checkoutManager->activePaypalPlan($plan);
        $this->assertEquals($payPalPlan['state'], 'ACTIVE');
        
        //create test actor
        $em = $client->getContainer()->get('doctrine')->getManager();
        $actor = new Actor();
        $uid = uniqid();
        $actor->setEmail('username'.$uid);
        $actor->setUsername('username'.$uid.'@email.com');
        $actor->setName('Test actor '.  $uid);
        $actor->setCity('City test');
        $actor->setAddress('Address 123.');
        $country = $em->getRepository('CoreBundle:Country')->find('es');
        $actor->setCountry($country);
        //crypt password
        $factory = $client->getContainer()->get('security.encoder_factory');
        $encoder = $factory->getEncoder(new Actor());
        $encodePassword = $encoder->encodePassword($uid, $actor->getSalt());
        $actor->setPassword($encodePassword);
        $em->persist($actor);
        //contract
        $contract = new Contract();
        $contract->setFinished(new DateTime('+1 year'));
        $contract->setActor($actor);
        $contract->setUrl('http://localhost/aviso-legal');
        $actor->addContract($contract);
        $em->persist($contract);
               
        $agree = new Agreement();
        $agree->setPlan($plan);
        $agree->setStatus('Created');
        $agree->setName('Test agreement '.uniqid());
        $agree->setDescription('Description of test agreement.');
        $agree->setPaymentMethod('credit_card');
        $agree->setContract($contract);
        $contract->setAgreement($agree);
        $em->persist($agree);
        $em->flush(); 
        //error
        $errors = $client->getContainer()->get('validator')->validate($agree);
        $this->assertEquals(count($errors), 0);
        
        
        //agreement
        $payPalAgreement = $checkoutManager->createPaypalAgreement($agree, array(
                        "number" => "4548812049400004",
                        "type" => "visa",
                        "expire_month" => 12,
                        "expire_year" => 2017,
                        "cvv2" => 123,
                        "first_name" => "Betsy",
                        "last_name" => "Buyer"
                   ));
        
//        print_r($payPalAgreement);
        $this->assertEquals($payPalAgreement['state'], 'Active');
        
        
    }

}
