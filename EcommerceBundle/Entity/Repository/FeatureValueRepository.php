<?php

namespace EcommerceBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * Class FeatureValueRepository
 */
class FeatureValueRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @param int $featureId
     *
     * @return int
     */
    public function countTotal($featureId=null)
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(fv)');

        if (!is_null($featureId)) {
            $qb->where('fv.feature = :feature_id')
                ->setParameter('feature_id', $featureId);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find all rows filtered for DataTables
     *
     * @param string $search        The search string
     * @param int    $sortColumn    The column to sort by
     * @param string $sortDirection The direction to sort the column
     * @param int    $featureId     The feature ID
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTables($search, $sortColumn, $sortDirection, $featureId)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('fv.id, fv.name');

        // where
        $qb->where('fv.feature = :feature_id')
            ->setParameter('feature_id', $featureId);

        // search
        if (!empty($search)) {
            $qb->andWhere('fv.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('fv.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('fv.name', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('EcommerceBundle:FeatureValue')
            ->createQueryBuilder('fv');

        return $qb;
    }
}