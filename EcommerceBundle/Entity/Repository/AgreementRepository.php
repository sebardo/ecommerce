<?php

namespace EcommerceBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;


class AgreementRepository  extends EntityRepository
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
            ->select('a.id, a.name');

        //join
        $qb->leftJoin('a.contract', 'c')
           ->leftJoin('a.plan', 'p')
                ;
        
        // search
        if (!empty($search)) {
            $qb->where('a.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('a.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('a.name', $sortDirection);
                break;
            case 2:
                $qb->orderBy('c.id', $sortDirection);
                break;
            case 3:
                $qb->orderBy('p.id', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }
    
    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();
        
        $qb = $em->getRepository('EcommerceBundle:Agreement')
            ->createQueryBuilder('a');
            
        return $qb;
    }

}
