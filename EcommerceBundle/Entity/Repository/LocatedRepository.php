<?php

namespace EcommerceBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;


/**
 * Class LocatedRepository
 */
class LocatedRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(l)');

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
            ->select('l.id, l.name, l.height, l.width, l.active');

  
        // search
        if (!empty($search)) {
            $qb->where('l.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('l.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('l.name', $sortDirection);
                break;
            case 2:
                $qb->orderBy('l.height', $sortDirection);
                break;
            case 3:
                $qb->orderBy('l.width', $sortDirection);
                break;
            case 4:
                $qb->orderBy('l.active', $sortDirection);
                break;
        }

        if($sortColumn=='') $qb->orderBy('l.id', 'ASC');
        return $qb->getQuery();
    }

    

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('EcommerceBundle:Located')
            ->createQueryBuilder('l');

        return $qb;
    }
}