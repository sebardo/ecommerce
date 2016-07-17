<?php

namespace EcommerceBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use ore\Bundle\EcommerceBundle\Entity\Category;


/**
 * Class CategoryRepository
 */
class CategoryRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @param int|null $categoryId The category ID
     *
     * @return int
     */
    public function countTotal($categoryId = null)
    {
        $qb = $this->getQueryBuilder()
            ->select('COUNT(c)')
            ->innerJoin('c.family', 'f');

        if (!is_null($categoryId)) {
            $qb->where('c.parentCategory = :category_id')
                ->setParameter('category_id', $categoryId);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Find all rows filtered for DataTables
     *
     * @param string   $search        The search string
     * @param int      $sortColumn    The column to sort by
     * @param string   $sortDirection The direction to sort the column
     * @param int|null $categoryId    The category ID
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTables($search, $sortColumn, $sortDirection)
    {
        $qb = $this->getQueryBuilder();

       
            // select
            $qb->select('c.id, c.name, c.order, c.active');
                //->leftJoin('c.family', 'f');
       

            // where
            $qb->where('c.parentCategory IS NULL ');
        

        // search
        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('c.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('c.name', $sortDirection);
                break;
            case 2:
                $qb->orderBy('c.order', $sortDirection);
                break;
            case 3:
                $qb->orderBy('c.active', $sortDirection);
                break;
        }

        if($sortColumn=='') $qb->orderBy('c.order', 'ASC');
        return $qb->getQuery();
    }
    
     /**
     * Find all rows filtered for DataTables
     *
     * @param string   $search        The search string
     * @param int      $sortColumn    The column to sort by
     * @param string   $sortDirection The direction to sort the column
     * @param int|null $categoryId    The category ID
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTablesByActor($search, $sortColumn, $sortDirection, $actor)
    {
        $qb = $this->getQueryBuilder();

       
            // select
            $qb->select('c.id, c.name, c.order, c.active');
                //->leftJoin('c.family', 'f');
       
            //join
            $qb->leftJoin('c.actor', 'o');
                    
            // where
            $qb->where('c.parentCategory IS NULL ')
               ->andWhere('o.id LIKE :actor')
               ->setParameter('actor', $actor->getId());

        // search
        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('c.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('c.name', $sortDirection);
                break;
            case 2:
                $qb->orderBy('c.order', $sortDirection);
                break;
            case 3:
                $qb->orderBy('c.active', $sortDirection);
                break;
        }

        if($sortColumn=='') $qb->orderBy('c.order', 'ASC');
        return $qb->getQuery();
    }
    
    
    /**
     * Find all rows filtered for DataTables
     *
     * @param string   $search        The search string
     * @param int      $sortColumn    The column to sort by
     * @param string   $sortDirection The direction to sort the column
     * @param int|null $categoryId    The category ID
     *
     * @return \Doctrine\ORM\Query
     */
    public function findByCategoryIds($categoryIds)
    {
        $qb = $this->getQueryBuilder();

        
        // select
        $qb->select('c');

        // where
        $qb->where('c.id = :category_id')
            ->setParameter('category_id', $categoryId);

        //order
        $qb->orderBy('c.active', $sortDirection);
        

      
        return $qb->getQuery();
    }
    
    /**
     * Find all rows filtered for DataTables
     *
     * @param string   $search        The search string
     * @param int      $sortColumn    The column to sort by
     * @param string   $sortDirection The direction to sort the column
     * @param int|null $categoryId    The category ID
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTablesByCategory($search, $sortColumn, $sortDirection, $categoryId)
    {
        $qb = $this->getQueryBuilder();

        // this is a category
        if (is_null($categoryId)) {
            // select
            $qb->select('c.id, c.name, f.id familyId, f.name familyName, c.order, c.active')
                ->leftJoin('c.family', 'f');
        }
        // this is a subcategory
        else {
            // select
            $qb->select('c.id, c.name');

            // where
            $qb->where('c.parentCategory = :category_id')
                ->setParameter('category_id', $categoryId);
        }

        // search
        if (!empty($search)) {
            $qb->andWhere('c.name LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('c.id', $sortDirection);
                break;
            case 1:
                $qb->orderBy('c.name', $sortDirection);
                break;
            case 2:
                $qb->orderBy('c.order', $sortDirection);
                break;
            case 3:
                $qb->orderBy('c.active', $sortDirection);
                break;
        }

        if($sortColumn=='') $qb->orderBy('c.order', 'ASC');
        return $qb->getQuery();
    }


    /**
     * Find the next subcategory, or the first one if none is found
     *
     * @param Category $subcategory
     *
     * @return Category|null
     */
    public function findNextSubcategory($subcategory)
    {
        $qb = $this->getQueryBuilder()
            ->select('c')
            ->where('c.id > :id')
            ->andWhere('c.parentCategory = :parentCategory')
            ->orderBy('c.id', 'asc')
            ->setMaxResults(1)
            ->setParameter('id', $subcategory->getId())
            ->setParameter('parentCategory', $subcategory->getParentCategory());

        // get the first subcategory when there is no next
        if (0 == count($qb->getQuery()->getResult())) {
            $qb->where('c.id < :id')
                ->andWhere('c.parentCategory = :parentCategory')
                ->setParameter('id', $subcategory->getId())
                ->setParameter('parentCategory', $subcategory->getParentCategory());

            if (0 == count($qb->getQuery()->getResult())) {
                return null;
            }
        }

        return $qb->getQuery()
            ->getSingleResult();
    }

    /**
     * Get brands from their products relationship
     *
     * @param Category     $category
     * @param integer|null $limit
     *
     * @return ArrayCollection
     */
    public function getBrands($category, $limit = null)
    {
        $qb = $this->getQueryBuilder()
            ->select('DISTINCT b.id, b.name')
            ->innerJoin('c.products', 'p')
            ->innerJoin('p.brand', 'b')
            ->where('p.active = TRUE')
            ->andWhere('b.available = TRUE');

        if ($category->getFamily()) {
            // this is a category
            $qb->andWhere('c.parentCategory = :category')
                ->setParameter('category', $category);
        } else {
            // this is a subcategory
            $qb->andWhere('c = :category')
                ->setParameter('category', $category);
        }

        if (!is_null($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()
            ->getResult();
    }

    private function getQueryBuilder()
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('EcommerceBundle:Category')
            ->createQueryBuilder('c');

        return $qb;
    }
    /**
     * Return category_id for Assembly
     *
     * @return int
     */
    public function AssemblyCategory()
    {
        $category_id = $this->getEntityManager()->getRepository('EcommerceBundle:Category')->findByName("montaje");
        return $category_id;
    }
    
}