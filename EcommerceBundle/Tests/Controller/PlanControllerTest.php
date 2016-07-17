<?php

namespace EcommerceBundle\Tests\Controller;

use CoreBundle\Tests\CoreTest;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * @class  PlanControllerTest
 * @brief Test the  Plan entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/PlanControllerTest.php
 * @endcode
 */
class PlanControllerTest extends CoreTest
{
    
    /**
     * @code
     * phpunit -v --filter testPlan -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/PlanControllerTest.php
     * @endcode
     * 
     */
    public function testPlan()
    {
        //////////////////////////////////////////////////////////////////////////////////////////
        // Plan //////////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createPlan($uid);

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado el plan satisfactoriamente")')->count());
    }
}
