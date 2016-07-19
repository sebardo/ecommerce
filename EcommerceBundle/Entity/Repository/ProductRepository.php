<?php

namespace EcommerceBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use EcommerceBundle\Entity\Family;
use EcommerceBundle\Entity\Category;
use EcommerceBundle\Entity\Product;
use CoreBundle\Entity\Actor;


/**
 * Class ProductRepository
 */
class ProductRepository extends EntityRepository
{
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotal()
    {
        $qb = $this->getQueryBuilder(false)
            ->select('COUNT(p)');

        return $qb->getQuery()->getSingleScalarResult();
    }
    
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function getProductStats(Product $product, $start, $end)
    {
        $em = $this->getEntityManager();
        $repository = $em->getRepository('EcommerceBundle:ProductStats');
        $query = $repository->createQueryBuilder('s')
            ->select("s.id, p.id product, s.day, sum(s.visits) as visits")
            ->leftJoin('s.product', 'p')
            ->where('s.product = :product')
            ->andWhere('s.day >= :start')
            ->andWhere('s.day <= :end')
            ->setParameters(array(
                'product' => $product, 
                'start' => $start, 
                'end' => $end
                ))
            ->groupBy('s.day')   
            ->orderBy('s.id', 'ASC') 
            ->getQuery();

        $stats = $query->getResult();
        $arr = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($stats as $stat) {
            $arr->add($stat);
        }
        if(count($arr)==0){
            $element = array('product' => $product->getId(), 'day' => date('Y-m-d'), 'visits' => 0);
            $arr->add($element);
        }
        return $arr;
    }
    

    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countTotalStatsVisits()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT SUM( s.visits ) FROM  EcommerceBundle:ProductStats AS s'
        );

        $total = $query->getSingleScalarResult();

        return $total;
    }
    
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countVisitsByProduct()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT SUM(s.visits) as visit,  '
                . ' p.id, p.name , p.slug, o.latitude, o.longitude, '
                . ' c.id categoryId, c.name categoryName,  '
                . ' b.id brandId, b.name brandName, b.slug brandSlug,  '
                . ' i.path imagePath, '
                . ' p.price,  p.priceType, p.initPrice, p.discount, p.discountType, p.stock, p.active, p.available, p.highlighted, p.freeTransport,  '
                . ' o.id actorId, o.name actorName '
                . ' FROM EcommerceBundle:ProductStats as s '
                . ' LEFT JOIN s.product as p'
                . ' LEFT JOIN p.actor as o'
                . ' LEFT JOIN p.category as c'
                . ' LEFT JOIN p.brand as b '
                . ' LEFT JOIN p.images as i '
                . ' GROUP BY s.product ORDER BY visit DESC'
        );

        $visitas = $query->getResult();

        return $visitas;
    }
    
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countProductVisited()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT COUNT( DISTINCT s.product ) FROM  EcommerceBundle:ProductStats AS s'
        );

        $total = $query->getSingleScalarResult();

        return $total;
    }
    
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function countProductStatsLine()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT count(s.visits) as visit, s.day as day FROM EcommerceBundle:ProductStats as s GROUP BY s.day ORDER BY s.day ASC '
        );

        $stats = $query->getResult();

        return $stats;
    }
    
    /**
     * Count the total of rows
     *
     * @return int
     */
    public function bounceRate()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT COUNT( DISTINCT s.product ) FROM  EcommerceBundle:ProductStats AS s WHERE s.visits = :visits'
        )->setParameter('visits', '1');

        $total = $this->countProductVisited();
        $bounce = $query->getSingleScalarResult();
        $bounceRate =  (100*$bounce) / $total ;
        return number_format($bounceRate, 1);
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
        $qb = $this->getQueryBuilder(false)
            ->select('p.id, p.name, c.id categoryId, c.name categoryName, b.id brandId, b.name brandName, 
                      p.price, p.stock, p.active, p.available, p.highlighted, p.freeTransport, actor.id actorId, actor.name actorName');

        // join
        $qb->leftJoin('p.category', 'c')
//            ->join('c.parentCategory', 'pc')
            ->leftJoin('p.brand', 'b')
            ->leftJoin('p.actor', 'actor')
            ;

        // search
        if (!empty($search)) {
            $qb->andWhere('p.name LIKE :search OR
                        c.name LIKE :search OR
                        b.name LIKE :search OR 
                        actor.name LIKE :search' )
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('actor.name', $sortDirection);
                break;
            case 1:
                $qb->orderBy('p.name', $sortDirection);
                break;
            case 3:
                $qb->orderBy('c.name', $sortDirection);
                break;
            case 4:
                $qb->orderBy('b.name', $sortDirection);
                break;
            case 5:
                $qb->orderBy('p.price', $sortDirection);
                break;
            case 7:
                $qb->orderBy('p.stock', $sortDirection);
                break;
            case 8:
                $qb->orderBy('p.active', $sortDirection);
                break;
            case 9:
                $qb->orderBy('p.available', $sortDirection);
                break;
            case 10:
                $qb->orderBy('p.highlighted', $sortDirection);
                break;
            case 11:
                $qb->orderBy('p.freeTransport', $sortDirection);
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
     * @param entity $actor         The actor relation
     *
     * @return \Doctrine\ORM\Query
     */
    public function findAllForDataTablesByActor($search, $sortColumn, $sortDirection, Actor $actor)
    {
        // select
        $qb = $this->getQueryBuilder(false)
            ->select('p.id, p.name, c.id categoryId, c.name categoryName, b.id brandId, b.name brandName, 
                      p.price, p.stock, p.active, p.available, p.highlighted, p.freeTransport, actor.id actorId, actor.name actorName');

        // join
        $qb->join('p.category', 'c')
//            ->join('c.parentCategory', 'pc')
            ->join('p.brand', 'b')
            ->join('p.actor', 'actor')
            ;

         $qb->where('actor.id = :actor')
            ->setParameter('actor', $actor->getId());
        // search
        if (!empty($search)) {
            $qb->andWhere('p.name LIKE :search OR
                        c.name LIKE :search OR
                        b.name LIKE :search OR 
                        actor.name LIKE :search' )
                ->setParameter('search', '%'.$search.'%');
        }

        // sort by column
        switch($sortColumn) {
            case 0:
                $qb->orderBy('actor.name', $sortDirection);
                break;
            case 1:
                $qb->orderBy('p.name', $sortDirection);
                break;
            case 3:
                $qb->orderBy('c.name', $sortDirection);
                break;
            case 4:
                $qb->orderBy('b.name', $sortDirection);
                break;
            case 5:
                $qb->orderBy('p.price', $sortDirection);
                break;
            case 7:
                $qb->orderBy('p.stock', $sortDirection);
                break;
            case 8:
                $qb->orderBy('p.active', $sortDirection);
                break;
            case 9:
                $qb->orderBy('p.available', $sortDirection);
                break;
            case 10:
                $qb->orderBy('p.highlighted', $sortDirection);
                break;
            case 11:
                $qb->orderBy('p.freeTransport', $sortDirection);
                break;
        }

        return $qb->getQuery();
    }

    /**
     * Find a product from the same category
     *
     * @param Product $product
     * @param string  $direction
     *
     * @return Product|null
     */
    public function findSameCategoryProduct($product, $direction)
    {
        $qb = $this->getQueryBuilder()
            ->select('p')
            ->andWhere('p.category = :category')
            ->setMaxResults(1)
            ->setParameter('category', $product->getCategory());

        if ('next' == $direction) {
            $qb->andWhere('p.id > :id')
                ->orderBy('p.id', 'asc');
        } else {
            $qb->andWhere('p.id < :id')
                ->orderBy('p.id', 'desc');
        }

        $qb->setParameter('id', $product->getId());

        if (0 == count($qb->getQuery()->getResult())) {
            return null;
        }

        return $qb->getQuery()
            ->getSingleResult();
    }

    /**
     * Find new products
     *
     * @param Family|null $family
     * @param int|null    $limit
     *
     * @return ArrayCollection
     */
    public function findNews($family, $limit = null)
    {
        $qb = $this->getQueryBuilder()
            ->orderBy('p.highlighted', 'desc')
            ->addOrderBy('p.createdAt', 'desc');

        // filter by family
        if (!is_null($family)) {
            $qb->innerJoin('p.category', 'c')
                ->innerJoin('c.parentCategory', 'pc')
                ->andWhere('pc.family = :family')
                ->setParameter('family', $family);
        }

        if (!is_null($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Find offers
     *
     * @param Family|null $family
     * @param int|null    $limit
     *
     * @return ArrayCollection
     */
    public function findOffers($family, $limit = null)
    {
        $qb = $this->getQueryBuilder()
            ->andWhere('p.discount IS NOT NULL')
            ->orderBy('p.discount', 'desc');

        // filter by family
        if (!is_null($family)) {
            $qb->innerJoin('p.category', 'c')
                ->innerJoin('c.parentCategory', 'pc')
                ->andWhere('pc.family = :family')
                ->setParameter('family', $family);
        }

        if (!is_null($limit)) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Find all products by filters
     *
     * @param Category $category
     * @param array    $options
     * @param bool     $getResults
     *
     * @return ArrayCollection|Query
     */
    public function findAllByFilters($category, Array $options, $getResults = true)
    {
        $qb = $this->getQueryBuilder()
            ->select('p');

        if (is_null($category) && array_key_exists('s-with-dashes', $options)) {
            // searching
            $orX = $qb->expr()->orX();
            $orX->add('p.name LIKE :searchWithDashes')
                ->add('p.name LIKE :searchWithSpaces');
            $qb->andWhere($orX)
                ->setParameter('searchWithDashes', '%'.$options['s-with-dashes'].'%')
                ->setParameter('searchWithSpaces', '%'.$options['s-with-spaces'].'%');
        } elseif (!is_null($category)) {
            if (!is_null($category->getParentCategory())) {
                // listing by subcategory (put in 'where' as it is)
                $qb->andWhere('p.category = :category')
                    ->setParameter('category', $category);
            } else {
                // listing by category (put its subcategories in a chained 'or')
                $orX = $qb->expr()->orX();

                foreach ($category->getSubcategories() as $subcategory) {
                    $orX->add('IDENTITY(p.category) = '.$subcategory->getId());
                }

                $qb->andWhere($orX);
            }
        }

        // sort filter
        if (false === array_key_exists('sort', $options)) {
            $options['sort'] = 'price-asc';
        }

        switch ($options['sort']) {
            case 'newest':
                $qb->orderBy('p.createdAt', 'desc');
                break;
            case 'oldest':
                $qb->orderBy('p.createdAt', 'asc');
                break;
            case 'price-asc':
                $qb->orderBy('p.price', 'asc');
                break;
            case 'price-desc':
                $qb->orderBy('p.price', 'desc');
                break;
        }

        // brands filter
        if (array_key_exists('brands', $options)) {
            $orX = $qb->expr()->orX();

            foreach ($options['brands'] as $brand) {
                $orX->add('IDENTITY(p.brand) = '.$brand);
            }

            $qb->andWhere($orX);
        }

        // attributes filter
        if (array_key_exists('attributes', $options)) {
            $qb->join('p.attributeValues', 'av');

            $orX = $qb->expr()->orX();

            foreach ($options['attributes'] as $attribute) {
                $orX->add('av.id = '.$attribute);
            }

            $qb->andWhere($orX);
        }

        // features filter
        if (array_key_exists('features', $options)) {
            $qb->join('p.featureValues', 'fv');

            $orX = $qb->expr()->orX();

            foreach ($options['features'] as $feature) {
                $orX->add('fv.id = '.$feature);
            }

            $qb->andWhere($orX);
            $qb->having('count(p.id) >= :num_features');
            $numFeatures = count($options['features']);
            $qb->setParameter('num_features', $numFeatures);
        }
        $qb->groupBy('p.id');

        // attribute ranges filter
/*        if (array_key_exists('attribute-min', $options) && array_key_exists('attribute-max', $options)) {
            if (false === array_key_exists('attributes', $options)) {
                $qb->join('p.attributeValues', 'av');
            }

            $andX = $qb->expr()->andX();

            foreach ($options['attribute-min'] as $value) {
                $andX->add('av.name >= '.$value);
            }

            foreach ($options['attribute-max'] as $value) {
                $andX->add('av.name <= '.$value);
            }

            $qb->andWhere($andX);
        }*/

        // feature ranges filter
/*        if (array_key_exists('feature-min', $options) && array_key_exists('feature-max', $options)) {
            if (false === array_key_exists('features', $options)) {
                $qb->join('p.featureValues', 'fv');
            }

            $andX = $qb->expr()->andX();

            foreach ($options['feature-min'] as $value) {
                $andX->add('fv.name >= '.$value);
            }

            foreach ($options['feature-max'] as $value) {
                $andX->add('fv.name <= '.$value);
            }

            $qb->andWhere($andX);
        }*/

        $query = $qb->getQuery();

        return $getResults ? $query->getResult() : $query;
    }

    /**
     * Find sorted attribute values
     *
     * @param Product $product
     *
     * @return ArrayCollection
     */
    public function findAttributeValues($product)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('a.name attributeName, av.name, i.path imagePath');

        // join
        $qb->join('p.attributeValues', 'av')
            ->join('av.attribute', 'a')
            ->leftJoin('av.image', 'i');

        // where
        $qb->andWhere('p = :product')
            ->setParameter('product', $product);

        // order by
        $qb->orderBy('a.order', 'asc');

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * Find sorted feature values
     *
     * @param Product $product
     *
     * @return ArrayCollection
     */
    public function findFeatureValues($product)
    {
        // select
        $qb = $this->getQueryBuilder()
            ->select('f.name featureName, fv.name, i.path imagePath');

        // join
        $qb->join('p.featureValues', 'fv')
            ->join('fv.feature', 'f')
            ->leftJoin('fv.image', 'i');

        // where
        $qb->andWhere('p = :product')
            ->setParameter('product', $product);

        // order by
        $qb->orderBy('f.order', 'asc');

        return $qb->getQuery()
            ->getResult();
    }

    /**
     * @param bool $onlyActive
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder($onlyActive = true)
    {
        $em = $this->getEntityManager();

        $qb = $em->getRepository('EcommerceBundle:Product')
            ->createQueryBuilder('p');

        if ($onlyActive) {
            $qb->where('p.active = true');
        }

        return $qb;
    }
    
    /**
     * Return products of the assembly category
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryProductsAssembly()
    {
        $category_id = $this->getEntityManager()->getRepository('EcommerceBundle:Category')->AssemblyCategory();
        return $this->createQueryBuilder('p')
            ->where('p.category = :category_id')
            ->setParameter('category_id', $category_id);
    }    
}