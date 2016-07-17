<?php

namespace EcommerceBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use CoreBundle\Entity\Actor;

/**
 * Class AdvertRepository
 */
class AdvertRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(a)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Count the total of rows
     *
     * @return int
     */
    public function getAdverts($start, $end, $section=null, $geolocated=null, $postalCode=null, $city=null, $brand=null)
    {
//        var_dump($start, $end, $section, $geolocated, $postalCode, $city, $brand); 
        //section null is brand page advert
        if(is_null($section))
         {
            if(!is_null($postalCode) && is_array($brand)){   
                $count3=0;
                $qb = $this->getQueryBuilder() 
                    ->leftJoin('a.brand', 'b')
                    ->leftJoin('a.postalCodes', 'p')
                    ->where('a.from <= :start')
                    ->andWhere('a.to >= :end')
                    ->andWhere('a.active = :active')
                    ->andWhere("a.geolocated = :geolocated OR a.geolocated = 'all' ")
                    ->andWhere("p.postalCode = :postalCode")
                    ->andWhere("b.id IN (:brand) ")
                    ->setParameters(array(
                        'start' => $start, 
                        'end' => $end,
                        'active' => true,
                        'geolocated' => $geolocated,//if postal code not null always geolocated
                        'postalCode'=> $postalCode,
                        'brand' => array_values($brand)
                        ));
                $query = $qb->getQuery();
                $count3 = count($query->getResult());
                
                if($count3 == 0){
                    $qb4 = $this->getQueryBuilder() 
                        ->leftJoin('a.brand', 'b')
                        ->leftJoin('a.postalCodes', 'p')
                        ->where('a.from <= :start')
                        ->andWhere('a.to >= :end')
                        ->andWhere('a.active = :active')
                        ->andWhere("a.geolocated = :geolocated OR a.geolocated = 'all' ")
                        ->andWhere("b.id IN (:brand)")
                        ->setParameters(array(
                            'start' => $start, 
                            'end' => $end,
                            'active' => true,
                            'geolocated' => $geolocated,//if postal code not null always geolocated
                            'brand' => array_values($brand)
                            ));
                    $query = $qb4->getQuery();
                }
                
            }
        }else{
            //SECTIONS
            $count=0;
            if(!is_null($postalCode)){  
                
                $qb = $this->getQueryBuilder() 
                    ->join('a.located', 'l')
                    ->leftJoin('a.postalCodes', 'p')
                    ->where('l.name = :section')
                    ->andWhere('a.from <= :start')
                    ->andWhere('a.to >= :end')
                    ->andWhere('a.active = :active')
                    ->andWhere("a.geolocated = :geolocated OR a.geolocated = 'all' ")
                    ->andWhere("p.postalCode = :postalCode ")
                    ->setParameters(array(
                        'section' => $section, 
                        'start' => $start, 
                        'end' => $end,
                        'active' => true,
                        'geolocated' => $geolocated,//if postal code not null always geolocated
                        'postalCode'=> $postalCode
                        ));
                //get home postalcode + brand priority=2
                if($brand === true){
                    $qb->join('a.brand', 'b');
                }elseif($brand === false){
                    $qb->andWhere('a.brand is NULL');
                }

                $query = $qb->getQuery();
                $count = count($query->getResult());
                
            }
            
            if($count == 0){
                $count2 = 0;
                
                $qb2 = $this->getQueryBuilder() 
                ->join('a.located', 'l')
                ->where('l.name = :section')
                ->andWhere('a.from <= :start')
                ->andWhere('a.to >= :end')
                ->andWhere('a.active = :active')
                ->setParameters(array(
                    'section' => $section, 
                    'start' => $start, 
                    'end' => $end,
                    'active' => true,
                    ));
                //just add geolocated filter when is geolocated
                //when is not geolocated return all advert without this filter
                if($geolocated){
                    $qb2->andWhere("a.geolocated = :geolocated OR a.geolocated = 'all' ")
                       ->setParameter('geolocated', $geolocated);
                }
                if(!is_null($city)){
                    
                    $qb2->orWhere("l.name = :section and a.from <= :start and a.to >= :end and a.active = :active and a.cities LIKE :cities ")
                       ->setParameter('cities', '%'.$city.'%')
                       ->setMaxResults(1)
                       ->orderBy('a.cities', 'DESC'); 
                    $query = $qb2->getQuery();
                    $count2 = count($query->getResult());
//                    print_r($count2);
                    if($count2 == 0){
                        $qb3 = $this->getQueryBuilder() 
                            ->join('a.located', 'l')
                            ->where('l.name = :section')
                            ->andWhere('a.from <= :start')
                            ->andWhere('a.to >= :end')
                            ->andWhere('a.active = :active')
                            ->setParameters(array(
                                'section' => $section, 
                                'start' => $start, 
                                'end' => $end,
                                'active' => true,
                                ));
                        if(!is_null($geolocated)){
                            $qb3->andWhere("a.geolocated = :geolocated OR a.geolocated = 'all' ")
                               ->setParameter('geolocated', $geolocated);
                        }
                        if(!is_null($brand)){
                            if($brand === true){
                                $qb3->join('a.brand', 'b');
                            }elseif($brand === false){
                                $qb3->andWhere('a.brand is NULL');
                            }else{
                                $qb3->leftJoin('a.brand', 'b')
                                ->andWhere("b.id IN (:brand) ")
                                ->setParameter('brand', array_values($brand));
                            }
                        }
                        $query = $qb3->getQuery();
                    }
                }else{
                    if(!is_null($brand)){
                        if($brand === true){
                            $qb2->join('a.brand', 'b');
                        }elseif($brand === false){
                            $qb2->andWhere('a.brand is NULL');
                        }else{
                            $qb2->leftJoin('a.brand', 'b')
                            ->andWhere("b.id IN (:brand) ")
                           ->setParameter('brand', array_values($brand));
                       }

                    } 
                }

                
                $query = $qb2->getQuery();
            }
        }
        
        
        

        //->orderBy('s.day', 'ASC')
        $adverts = array();
        if(is_object($query)){
            $adverts = $query->getResult();
        }
        
        
        $arr = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($adverts as $ad) {
            $arr->add($ad);
        }
        
        return $arr;
    }
    
    /**
     * Find all rows filtered for DataTables
     *
     * @param string $search        The search string
     * @param int    $sortColumn    The column to sort by
     * @param string $sortDirection The direction to sort the column
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTables($search, $sortColumn, $sortDirection)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('a.id, a.title, a.description, a.geolocated');

  
        // search
        if (!empty($search)) {
            $qb->where('a.title LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('a.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('a.title', $sortDirection);
                break;
            case 2:
                $qb->orderBy('a.description', $sortDirection);
                break;
            case 3:
                $qb->orderBy('a.width', $sortDirection);
                break;
        }

        if($sortColumn=='') $qb->orderBy('a.id', 'ASC');
        return $qb->getQuery();
    }

    /**
     * Find all rows filtered for DataTables
     *
     * @param string $search        The search string
     * @param int    $sortColumn    The column to sort by
     * @param string $sortDirection The direction to sort the column
     * @param int    $actorId        The actor ID
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTablesByActor($search, $sortColumn, $sortDirection, Actor $actor)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('a.id, a.title, a.description, a.geolocated, IDENTITY(a.actor) actorId, o.name actorName');
   
        // join
        $qb->leftJoin('a.actor', 'o');

        // where
        $qb->where('o.id = :actor')
            ->setParameter('actor', $actor);

        // search
        if (!empty($search)) {
            $qb->andWhere('a.title = :search')
                ->setParameter('search', $search);
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('a.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('a.title', $sortDirection);
                break;
            case 4:
                $qb->orderBy('a.description', $sortDirection);
                break;
        }

        // group by
        $qb->groupBy('a.id');

        return $qb->getQuery();
    }

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('EcommerceBundle:Advert')
            ->createQueryBuilder('a');

        return $qb;
    }
}