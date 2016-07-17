<?php

namespace EcommerceBundle\Tests\Controller;

use CoreBundle\Tests\CoreTest;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  LocatedControllerTest
 * @brief Test the  Located entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/LocatedControllerTest.php
 * @endcode
 */
class LocatedControllerTest  extends CoreTest
{
    /**
     * @code
     * phpunit -v --filter testLocated -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/LocatedControllerTest.php
     * @endcode
     * 
     */
    public function testLocated()
    {
        //////////////////////////////////////////////////////////////////////////////
        // Brand ///////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createLocated($uid);

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Show/////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click edit///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Editar")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar located '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['located[name]'] = 'located '.$uid;
        $form['located[height]'] = '235px';
        $form['located[width]'] = '235px';
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("located '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado la ubicación satisfactoriamente")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado la ubicación satisfactoriamente")')->count());
    }
    
  
}
