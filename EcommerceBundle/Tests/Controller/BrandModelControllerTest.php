<?php

namespace EcommerceBundle\Tests\Controller;

use CoreBundle\Tests\CoreTest;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  BrandModelControllerTest
 * @brief Test the  BrandModel entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/BrandModelControllerTest.php
 * @endcode
 */
class BrandModelControllerTest  extends CoreTest
{
    /**
     * @code
     * phpunit -v --filter testBrandModel -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/BrandModelControllerTest.php
     * @endcode
     * 
     */
    public function testBrandModel()
    {
        //////////////////////////////////////////////////////////////////////////////
        // Brand ///////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createBrand($uid);
        $brandName = 'brand '.$uid;
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $brand = $manager->getRepository('EcommerceBundle:Brand')->findOneByName($brandName);
        
        ////////////////////////////////////////////////////////////////////////////////////////
        // Model ///////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createBrandModel($uid, $brand);

        $link = $crawler
            ->filter('a:contains("Editar")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar brandmodel '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['brandmodel[name]'] = 'brandmodel '.$uid;
        $form['brandmodel[available]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("brandmodel '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado el modelo satisfactoriamente")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado el modelo satisfactoriamente")')->count());
    }
    
  
}
