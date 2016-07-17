<?php

namespace EcommerceBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * Class BrandRepository
 */
class BrandRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(b)');

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
            ->select('b.id, b.name, b.available');

        // search
        if (!empty($search)) {
            $qb->where('b.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('b.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('b.name', $sortDirection);
                break;
            case 2:
                $qb->orderBy('b.available', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }

    /**
     * Find all rows with images
     *
     * @param boolean $onlyAvailable
     *
     * @return array
     */
    public function findAllWithImages($onlyAvailable = true)
    {
        $qb = $this->getQueryBuilder()
            ->select('partial b.{id, name, slug}, partial i.{id, path}')
            ->innerJoin('b.image', 'i');

        if ($onlyAvailable) {
            $qb->where('b.available = TRUE');
        }

        return $qb->getQuery()
            ->getResult();
    }

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('EcommerceBundle:Brand')
            ->createQueryBuilder('b');

        return $qb;
    }
}