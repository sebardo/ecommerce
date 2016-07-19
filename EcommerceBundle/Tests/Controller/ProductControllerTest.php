<?php

namespace EcommerceBundle\Tests\Controller;

use CoreBundle\Tests\CoreTest;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  ProductControllerTest
 * @brief Test the  Product entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/ProductControllerTest.php
 * @endcode
 */
class ProductControllerTest extends CoreTest
{

    /**
     * @code
     * phpunit -v --filter testProductAdmin -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/ProductControllerTest.php
     * @endcode
     * 
     */
    public function testProductAdmin()
    {        
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        
        $uid = rand(999,9999);
        $crawler = $this->createProduct($uid, true);
        
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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar product '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $price = rand(99, 499);
        $form['product[name]'] = 'product '.$uid;
        $form['product[description]'] = 'product description'.$uid;
        $form['product[initPrice]'] = $price;
        $form['product[price]'] = $price;
        $form['product[priceType]'] = 0;
        $form['product[weight]'] = 2;
        $form['product[stock]'] = 10;
        $form['product[metaTitle]'] = 'Meta title_'.$uid;
        $form['product[metaDescription]'] = 'Meta description_'.$uid;
        $form['product[active]']->tick();
        $form['product[available]']->tick();
        $form['product[public]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("product '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado el producto satisfactoriamente")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado el producto satisfactoriamente")')->count());
       
    }
  
    /**
     * @code
     * phpunit -v --filter testProductActor -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/ProductControllerTest.php
     * @endcode
     * 
     */
    public function testProductActor()
    {        
        
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        
        $uid = rand(999,9999);
        $crawler = $this->createProduct($uid, false, false);
        
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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar product '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['product[name]'] = 'product '.$uid;
        $form['product[description]'] = 'product description'.$uid;
        $form['product[initPrice]'] = 50;
        $form['product[price]'] = 50;
        $form['product[priceType]'] = 0;
        $form['product[weight]'] = 2;
        $form['product[stock]'] = 10;
        $form['product[metaTitle]'] = 'Meta title_'.$uid;
        $form['product[metaDescription]'] = 'Meta description_'.$uid;
        $form['product[available]']->tick();
        $form['product[public]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("product '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado el producto satisfactoriamente")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado el producto satisfactoriamente")')->count());
        
    }
}
