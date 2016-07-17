<?php
namespace EcommerceBundle\DataFixtures\ORM;

use CoreBundle\DataFixtures\SqlScriptFixture;
use EcommerceBundle\Entity\Located;

class LoadAdvertData extends SqlScriptFixture
{
    public function createFixtures()
    {

        //INSERT INTO `advert` (`id`, `image_id`, `actor_id`,  `created`, `updated`, `from_date`, `to_date`, `title`, `description`, `geolocated`, `days`, `cities`, `slug`, `active`, `brand_id`) VALUES
        //CP(3, 3, 7761, '2016-06-09 20:32:18', '2016-06-09 20:32:18', '2016-06-08 20:32:17', '2016-06-20 20:32:17', 'titulo', '<p>asdasdasdasdasd</p>', 'all', '12', 'Cabrera de Mar', 'titulo', 1, NULL),
        //CITY(4, 4, 7761, '2016-06-09 20:33:07', '2016-06-09 21:08:29', '2016-06-08 20:33:07', '2016-06-20 20:33:07', 'titulo 2', '<p>asdasdasdasdasd 2</p>', '0', '12', 'Barcelona', 'titulo-2', 1, NULL),
        //BRAND(7, 7, 8035, '2016-06-10 16:41:39', '2016-06-10 16:41:39', '2016-06-08 16:41:36', '2016-06-30 16:41:36', 'titulo xz', '<p>asasassa as as as as</p>', 'all', '18', 'Cabrera de Mar,Madrid', 'titulo-xz', 1, 13);

        
        $categories = $this->getManager()->getRepository('EcommerceBundle:Category')->findBy(array('parentCategory' => NULL, 'active' => true ));
        
        $located = new Located();
        $located->setName('Home');  
        $located->setWidth('100%');
        $located->setHeight('300px');
        $this->getManager()->persist($located);
        $this->getManager()->flush();

        foreach ($categories as $category) {
            $located = new Located();
            $located->setName($category->getName());
            $located->setWidth('848px');
            $located->setHeight('234px');
            $this->getManager()->persist($located);
            $this->getManager()->flush();
        }

    }
    
    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
}

