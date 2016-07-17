<?php

namespace EcommerceBundle\Tests\Controller;

use CoreBundle\Tests\CoreTest;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  ContractControllerTest
 * @brief Test the  Contract entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/ContractControllerTest.php
 * @endcode
 */
class ContractControllerTest extends CoreTest
{

    /**
     * @code
     * phpunit -v --filter testContract -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/ContractControllerTest.php
     * @endcode
     * 
     */
    public function testContract()
    {
        ////////////////////////////////////////////////////////////////////////////
        // Actor ///////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createUser('actor', $uid);
        $username = 'actor+'.$uid.'@gmail.com';
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $actor = $manager->getRepository('CoreBundle:Actor')->findOneByEmail($username);
        
        //////////////////////////////////////////////////////////////////////////////////////////
        // Plan //////////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createPlan($uid);
        
        //////////////////////////////////////////////////////////////////////////////////////////
        // Contract //////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createContract($uid, $actor, $this->plan);
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click suspend contract //////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Suspender")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
   
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("El acuerdo ha sido suspendido satisfactoriamente")')->count());
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click reactive contract //////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Re-activar")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it

        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("El acuerdo ha sido re-activado satisfactoriamente")')->count());
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click cancel contract //////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Cancelar acuerdo")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("El acuerdo se ha cancelado satisfactoriamente")')->count());
    }
  
}
