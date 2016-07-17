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
 * phpunit -v -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/AdvertControllerTest.php
 * @endcode
 */
class AdvertControllerTest extends CoreTest
{

    /**
     * @code
     * phpunit -v --filter testAdvertAdminActor -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/AdvertControllerTest.php
     * @endcode
     * 
     */
    public function testAdvertAdminActor()
    {
        ////////////////////////////////////////////////////////////////////////////
        // Actor ///////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createUser('actor', $uid);
        $email = 'user+'.$uid.'@gmail.com';
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $actor = $manager->getRepository('CoreBundle:Actor')->findOneByEmail($email);
        
        //////////////////////////////////////////////////////////////////////////////////////////
        // Advert //////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createAdvert($uid, $actor);
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Show/////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        
        /////////////////////////////////////////////////////////////////////////////////////////
        //Click edit/////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////
        $link = $crawler
            ->filter('a:contains("Editar")') // find all links with the text "Greet"
            ->eq(0) // select the second link in the list
            ->link()
        ;
        $crawler = $this->client->click($link);// and click it
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar advert '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['advert[title]'] = 'advert '.$uid;
        $form['advert[active]']->tick();
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("advert '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado la publicidad satisfactoriamente")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado la publicidad satisfactoriamente")')->count());
    }
    
    /**
     * @code
     * phpunit -v --filter testAdvertActor -c app vendor/sebardo/ecommerce/EcommerceBundle/Tests/Controller/AdvertControllerTest.php
     * @endcode
     * 
     */
    public function testAdvertActor()
    {
        ////////////////////////////////////////////////////////////////////////////
        // Actor ///////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////
        $userId = rand(999,9999);
        $crawler = $this->createUser('actor', $userId);
        $username = 'actor+'.$userId.'@gmail.com';
        $container = $this->client->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $actor = $manager->getRepository('CoreBundle:Actor')->findOneByEmail($username);
        
        //////////////////////////////////////////////////////////////////////////////////////////
        // Advert //////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////////////////
        $uid = rand(999,9999);
        $crawler = $this->createAdvert($uid, $actor, $username, $userId, true);
        $advert = $manager->getRepository('EcommerceBundle:Advert')->findOneByTitle('advert '.$uid);
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        //Show/////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        
        /////////////////////////////////////////////////////////////////////////////////////////
        //Click edit/////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////
        $crawler = $this->client->request('GET', '/admin/advert/'.$advert->getId().'/edit', array(), array(), array(
            'PHP_AUTH_USER' => $username,
            'PHP_AUTH_PW'   => $userId,
        ));
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Editar advert '.$uid.'")')->count());
        
        //fill form
        $form = $crawler->selectButton('Guardar')->form();
        $uid = rand(999,9999);
        $form['advert[title]'] = 'advert '.$uid;
        $crawler = $this->client->submit($form);// submit the form
        
        //Asserts
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("advert '.$uid.'")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha editado la publicidad satisfactoriamente")')->count());

        ///////////////////////////////////////////////////////////////////////////////////////////
        //Click delete/////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        $form = $crawler->filter('form[id="delete-entity"]')->form();
        $crawler = $this->client->submit($form);// submit the form
        $this->assertTrue($this->client->getResponse() instanceof RedirectResponse);
        $crawler = $this->client->followRedirect();
        //Asserts
        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Se ha eliminado la publicidad satisfactoriamente")')->count());
    }
  
}
