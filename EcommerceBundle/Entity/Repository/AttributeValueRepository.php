<?php

namespace EcommerceBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;


/**
 * Class AttributeValueRepository
 */
class AttributeValueRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @param int $attributeId
     *
     * @return int
     */
    public function countTotal($attributeId)
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(av)');

        if (!is_null($attributeId)) {
            $qb->where('av.attribute = :attribute_id')
                ->setParameter('attribute_id', $attributeId);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find all rows filtered for DataTables
     *
     * @param string $search        The search string
     * @param int    $sortColumn    The column to sort by
     * @param string $sortDirection The direction to sort the column
     * @param int    $attributeId   The attribute ID
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTables($search, $sortColumn, $sortDirection, $attributeId)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('av.id, av.name');

        // where
        $qb->where('av.attribute = :attribute_id')
            ->setParameter('attribute_id', $attributeId);

        // search
        if (!empty($search)) {
            $qb->andWhere('av.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('av.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('av.name', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('EcommerceBundle:AttributeValue')
            ->createQueryBuilder('av');

        return $qb;
    }
}