<?php

namespace EcommerceBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;


class ContractRepository  extends EntityRepository
{

    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(c)');

        return $qb->getQuery()->getSingleScalarResult();
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
            ->select('c.id, a.id agreementId, a.name agreementName, a.status, c.url, c.created, c.finished, '
                    . 'o.id actorId, o.name actorName, '
                    . 'p.id planId, p.name planName');

        //join
        $qb->leftJoin('c.agreement', 'a')
           ->leftJoin('a.plan', 'p')
           ->leftJoin('c.actor', 'o')
                ;
        
        // search
        if (!empty($search)) {
            $qb->where('o.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('c.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('o.name', $sortDirection);
                break;
            case 2:
                $qb->orderBy('c.created', $sortDirection);
                break;
            case 3:
                $qb->orderBy('c.finished', $sortDirection);
                break;
            case 4:
                $qb->orderBy('a.status', $sortDirection);
                break;
        }

        return $qb->getQuery();
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
    public function findAllForDataTablesByActor($search, $sortColumn, $sortDirection, $actor)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('c.id, a.id agreementId, a.name agreementName, a.status, c.url, c.created, c.finished, o.id actorId, o.name actorName');

        //join
        $qb->leftJoin('c.agreement', 'a')
           ->leftJoin('c.actor', 'o')
                ;
        
        $qb->where('o.id LIKE :actor')
            ->setParameter('actor', $actor->getId());
        
        // search$actor
        if (!empty($search)) {
            $qb->andWhere('o.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('c.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('o.name', $sortDirection);
                break;
            case 2:
                $qb->orderBy('c.created', $sortDirection);
                break;
            case 3:
                $qb->orderBy('c.finished', $sortDirection);
                break;
            case 4:
                $qb->orderBy('a.status', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }
    
    
    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('EcommerceBundle:Contract')
            ->createQueryBuilder('c');
            
        return $qb;
    }

}
