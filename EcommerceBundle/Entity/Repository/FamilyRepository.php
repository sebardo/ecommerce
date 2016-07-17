<?php

namespace EcommerceBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;
use EcommerceBundle\Entity\Family;


/**
 * Class FamilyRepository
 */
class FamilyRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(f)');

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
            ->select('f.id, f.name, f.order');

        // search
        if (!empty($search)) {
            $qb->where('f.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('f.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('f.name', $sortDirection);
                break;
            case 2:
                $qb->orderBy('f.order', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }

    /**
     * Find all rows with their related categories
     *
     * @return array
     */
    public function findAllWithCategories()
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('f, c')
            ->innerJoin('f.categories', 'c')
            ->innerJoin('c.subcategories', 's');

        // sort by family and category id
        $qb->orderBy('f.order', 'asc')
            ->addOrderBy('c.id', 'asc');

        return $qb->getQuery()
            ->getResult();
    }

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('EcommerceBundle:Family')
            ->createQueryBuilder('f');

        return $qb;
    }
}