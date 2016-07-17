<?php

namespace EcommerceBundle\Tests\Controller;

use CoreBundle\Tests\CoreTest;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @class  FeatureControllerTest
 * @brief Test the  Feature entity
 *
 * To run the testcase:
 * @code
 * phpunit -v -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/FeatureControllerTest.php
 * @endcode
 */
class FeatureControllerTest  extends CoreTest
{
    /**
     * @code
     * phpunit -v --filter testFeature -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/FeatureControllerTest.php
     * @endcode
     * 
     */
    public function testFeatureAdmin()
    {
        
        //////////////////////////////////////////////////////////////////////////////
        // Category ///////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createCategory($uid);
        
        /////////////////////////////////////////////////////////////////////////////////
        // Feature //////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////
       
        //index
        $crawler = $this->client->request('GET', '/admin/features/', array(), array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Características")')->count());
      
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click new ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Añadir nueva")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nueva característica")')->count());
   
        //get entity 
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $entity = $manager->getRepository('EcommerceBundle:Category')->findOneByName('category '.$uid);
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['feature[name]'] = 'feature '.$uid;
        $form['feature[category]']->select($entity->getId());
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("feature '.$uid.'")')->count());

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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar feature '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['feature[name]'] = 'feature '.$uid;
        //$form['feature[category]']->select($entity->getId());
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("feature '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado la característica satisfactoriamente")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado la característica satisfactoriamente")')->count());
    }
    
     /**
     * @code
     * phpunit -v --filter testFeatureActor -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/FeatureControllerTest.php
     * @endcode
     * 
     */
    public function testFeatureActor()
    {
        
        ////////////////////////////////////////////////////////////////////////////
        // Actor ///////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createUser('actor', $uid);
        $username = 'actor+'.$uid.'@gmail.com';
        $password = $uid;
        
        //////////////////////////////////////////////////////////////////////////////
        // Category ///////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createCategory($uid, $username, $password);
        
        /////////////////////////////////////////////////////////////////////////////////
        // Feature //////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////
        //index
        $crawler = $this->client->request('GET', '/admin/features/', array(), array(), array(
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW'   => $password,
        ));     
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Características")')->count());
      
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click new ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Añadir nueva")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Nueva característica")')->count());
   
        //get entity 
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $entity = $manager->getRepository('EcommerceBundle:Category')->findOneByName('category '.$uid);
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['feature[name]'] = 'feature '.$uid;
        $form['feature[category]']->select($entity->getId());
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("feature '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha creado la característica satisfactoriamente")')->count());


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
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar feature '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['feature[name]'] = 'feature '.$uid;
        //$form['feature[category]']->select($entity->getId());
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("feature '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado la característica satisfactoriamente")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado la característica satisfactoriamente")')->count());
    }
  
}
