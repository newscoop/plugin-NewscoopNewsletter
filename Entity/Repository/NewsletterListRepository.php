<?php
/**
 * @package Newscoop\NewsletterPluginBundle
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Newscoop\NewsletterPluginBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Newscoop\NewsletterPluginBundle\TemplateList\ListCriteria;
use Newscoop\ListResult;

/**
 * Newsletter list Repository
 */
class NewsletterListRepository extends EntityRepository
{
    /**
     * Get list for given criteria
     *
     * @param  ListCriteria        $criteria
     * @return Newscoop\ListResult
     */
    public function getListByCriteria(ListCriteria $criteria)
    {
        $qb = $this->createQueryBuilder('nl');
        $qb
            ->select('nl', 'g')
            ->leftJoin('nl.groups', 'g')
            ->where('nl.is_active = :is_active')
            ->setParameter('is_active', true);

        foreach ($criteria->perametersOperators as $key => $operator) {
            if ($criteria->$key == "true" || $criteria->$key == "false") {
                $criteria->$key = (bool) $criteria->$key;
            }

            $qb->andWhere('nl.'.$key.' = :'.$key)
                ->setParameter($key, $criteria->$key);
        }

        $list = new ListResult();
        $countBuilder = clone $qb;
        $list->count = (int) $countBuilder->select('COUNT(nl)')->getQuery()->getSingleScalarResult();

        if ($criteria->length != 0) {
            $qb->setMaxResults($criteria->length);
        }

        $metadata = $this->getClassMetadata();
        foreach ($criteria->orderBy as $key => $order) {
            if (array_key_exists($key, $metadata->columnNames)) {
                $key = 'nl.' . $key;
            }

            $qb->orderBy($key, $order);
        }

        $list->items = $qb->getQuery()->getResult();

        return $list;
    }

    /**
     * Get newsletter lists count by given criteria
     *
     * @param array $criteria
     * @return int
     */
    public function findByCount(array $criteria)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(c)')
            ->from($this->getEntityName(), 'c');

        foreach ($criteria as $property => $value) {
            if (!is_array($value)) {
                $queryBuilder->andWhere("u.$property = :$property");
            }
        }

        $query = $queryBuilder->getQuery();
        foreach ($criteria as $property => $value) {
            if (!is_array($value)) {
                $query->setParameter($property, $value);
            }
        }

        return (int) $query->getSingleScalarResult();
    }
}