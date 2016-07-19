<?php

namespace EcommerceBundle\Entity\Repository;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;


class PlanRepository  extends EntityRepository
{

    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(p)');

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
            ->select('p.id, p.paypalId, p.name,  p.state, p.setupAmount, p.amount, p.cycles, p.frequency, p.frequencyInterval , '
                    . 'p.trialAmount, p.trialCycles, p.trialFrequency, p.trialFrequencyInterval ,'
                    . ' p.created ');
        
        // search
        if (!empty($search)) {
            $qb->where('p.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('p.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('p.name', $sortDirection);
                break;
            case 5:
                $qb->orderBy('p.created', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }
    
    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();
        
        $qb = $em->getRepository('EcommerceBundle:Plan')
            ->createQueryBuilder('p');
            
        return $qb;
    }

}
