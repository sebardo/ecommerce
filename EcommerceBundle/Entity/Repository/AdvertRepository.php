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
    public function getAdverts($start, $end, $section=null)
    {
    
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

            $query = $qb2->getQuery();
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
            ->select('a.id, a.title, a.description');

  
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
            ->select('a.id, a.title, a.description, IDENTITY(a.actor) actorId, o.name actorName');
   
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