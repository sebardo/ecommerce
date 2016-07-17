<?php

namespace EcommerceBundle\Tests\Controller;

//require_once(__DIR__."/../../../../../tools/pdf2text/pdf2text.php");

use CoreBundle\Tests\CoreTest;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  InvoiceControllerTest
 * @brief Test the  Invoice entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/InvoiceControllerTest.php
 * @endcode
 */
class InvoiceControllerTest extends CoreTest
{

    /**
     * @code
     * phpunit -v --filter testInvoiceStandar -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/InvoiceControllerTest.php
     * @endcode
     * 
     */
    public function testInvoiceStandar()
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

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click download invoice///////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
//        $link = $crawler
//            ->filter('a:contains("descargar PDF")') // find all links with the text "Greet"
//            ->eq(0) // select the second link in the list
//            ->link()
//        ;
//        $crawler = $this->client->click($link);// and click it
//        $this->assertTrue($this->client->getResponse()->isSuccessful());
         
//        $url = $link->getUri();
//        $this->getTransaction();
//        //Create a client test to request
//         $this->client->request( 'GET',
//                $url,
//                $parameters = array(),
//                $files = array(),
//                $server = array('CONTENT_TYPE' => 'application/pdf'));
//        $statusCode = $this->client->getResponse()->getStatusCode();
//        $output = $this->client->getResponse()->getContent();
//
//        $nombre_archivo = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->transaction->getId().".pdf";
//        file_put_contents($nombre_archivo, $output);
//        $result = pdf2text($nombre_archivo);
//
//        print_r($url);die();
//        $this->assertTrue(\preg_match('/http:\/\/www.devispresto.com/', $result)==1);
        
            
    }
  
    
   
}
