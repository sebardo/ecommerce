<?php

namespace EcommerceBundle\Tests\Controller;

use CoreBundle\Tests\CoreTest;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  TransactionControllerTest
 * @brief Test the  Transaction entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/TransactionControllerTest.php
 * @endcode
 */
class TransactionControllerTest extends CoreTest
{

    /**
     * @code
     * phpunit -v --filter testTransactionStandar -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/TransactionControllerTest.php
     * @endcode
     * 
     */
    public function testTransactionStandar()
    {        
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        
        //new product
        $productId = rand(999,9999);
        $crawler = $this->createProduct($productId, true);
        $product = $manager->getRepository('EcommerceBundle:Product')->findOneByName('product '.$productId);
        
        //front product show
        $crawler = $this->client->request('GET', '/logout');
        $crawler = $this->client->request('GET', '/products/'.$product->getSlug());
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("product '.$productId.'")')->count());
        
        //click buy
        $form = $crawler->filter('form[name="cartitem"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Tienes 1 producto en el carrito")')->count());
        
        //click pay
        $form = $crawler->filter('form[name="cart"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Identificación")')->count());
       
        //register user
        $uid = rand(999,9999);
        $crawler = $this->registerUser($uid, $crawler);
        
        //click pay again
        $form = $crawler->filter('form[name="cart"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        
        //Asserts redirection delivery-info
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Datos para el envío")')->count());
        
        //fill delivery info
        $deliveryId = rand(999,9999);
        $crawler = $this->fillDeliveryInfo($deliveryId, $crawler);
       
        //fill sumary form
        $summaryId = rand(999,9999);
        $crawler = $this->fillSummary($summaryId, $crawler);
        //Asserts redirection confirmation 
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Confirmación de pago")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Estimado cliente, su pago se ha realizado correctamente")')->count());
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click invoice////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Ver factura")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("FACTURA")')->count());
        //check delivery info
        $crawler = $this->checkDeliveryInfo($deliveryId, $crawler);
        //check summary info
        $crawler = $this->checkSummary($productId, $crawler);
        
        ///////////////////////////////////////////////////////////////////////////
        // TRANSPORT CODE ////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////
        //login as actor (product owner)
        $crawler = $this->client->request('GET', '/logout');
        $this->getTransaction();
        $crawler = $this->client->request('GET', '/admin/transaction/'. $this->transaction->getId(), array(), array(), array(
                'PHP_AUTH_USER' => $this->actor->getEmail(),
                'PHP_AUTH_PW'   => $this->password,
            ));
        
        //set traking code
        $form = $crawler->filter('form[name="traking_code"]')->form();
        $trakingCode = rand(999,9999);
        $form['form[trackingCode]'] = $trakingCode;
        $crawler = $this->client->submit($form);// submit the form
        //assert
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("'.$trakingCode.'")')->count());
        
            
    }
  
    
   
}
