<?php

namespace EcommerceBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * Class AttributeRepository
 */
class AttributeRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @param int $categoryId
     *
     * @return int
     */
    public function countTotal($categoryId = null)
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(a)');

        if (!is_null($categoryId)) {
            $qb->where('a.category = :category_id')
                ->setParameter('category_id', $categoryId);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find all rows filtered for DataTables
     *
     * @param string $search        The search string
     * @param int    $sortColumn    The column to sort by
     * @param string $sortDirection The direction to sort the column
     * @param int    $categoryId    The category ID
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTables($search, $sortColumn, $sortDirection)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('a.id, a.name, a.order, a.filtrable, c.id categoryId, c.name categoryName, f.id familyId, f.name familyName');

        // join
        $qb->leftJoin('a.category', 'c')
            ->leftJoin('c.family', 'f');

        // search
        if (!empty($search)) {
            $qb->andWhere('a.name LIKE :search')
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
                $qb->orderBy('a.order', $sortDirection);
                break;
            case 3:
                $qb->orderBy('a.filtrable', $sortDirection);
                break;
            case 4:
                $qb->orderBy('c.name', $sortDirection);
                break;
            case 5:
                $qb->orderBy('f.name', $sortDirection);
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
     * @param int    $categoryId    The category ID
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTablesByCategory($search, $sortColumn, $sortDirection, $categoryId)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('a.id, a.name, a.order, a.filtrable, c.id categoryId, c.name categoryName, f.id familyId, f.name familyName');

        // join
        $qb->leftJoin('a.category', 'c')
            ->leftJoin('c.family', 'f');

        // where
        if (!is_null($categoryId)) {
            $qb->where('a.category = :category_id')
                ->setParameter('category_id', $categoryId);
        }

        // search
        if (!empty($search)) {
            $qb->andWhere('a.name LIKE :search')
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
                $qb->orderBy('a.order', $sortDirection);
                break;
            case 3:
                $qb->orderBy('a.filtrable', $sortDirection);
                break;
            case 4:
                $qb->orderBy('c.name', $sortDirection);
                break;
            case 5:
                $qb->orderBy('f.name', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('EcommerceBundle:Attribute')
            ->createQueryBuilder('a');

        return $qb;
    }
}