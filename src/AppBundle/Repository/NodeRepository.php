<?php

namespace AppBundle\Repository;

/**
 * NodeRepository
 *
 */
class NodeRepository extends \Doctrine\ORM\EntityRepository
{
	public function findById($id)
	{
		$qb = $this->createQueryBuilder('n');

		$qb->select('n')
		   ->leftJoin('n.children', 'children')
		   ->leftJoin('children.validations', 'childrenValidations')
		   ->leftJoin('n.validations', 'validations')
		   ->addSelect('children')
		   ->addSelect('childrenValidations')
		   ->addSelect('validations');
		   ->where('n.id = :id')
		   ->setParameter('id', $id)
		;

		return $qb->getQuery()->getArrayResult();
	}

	public function findByIds($ids)
	{
		$qb = $this->createQueryBuilder('n');

		$qb->select('n')
		   ->leftJoin('n.children', 'children')
		   ->leftJoin('children.validations', 'childrenValidations')
		   ->leftJoin('n.validations', 'validations')
		   ->addSelect('children')
		   ->addSelect('childrenValidations')
		   ->addSelect('validations');
		
		$qb->where($qb->expr()->in('n.id', $ids));

		return $qb->getQuery()->getArrayResult();
	}

	public function findByTerm($term)
	{
		$qb = $this->createQueryBuilder('n');

		$qb->select('n')
			->where('n.name LIKE :term')
			->andWhere('n.level != :idiot')
			->setParameter('term', '%'.$term.'%')
			->setParameter('idiot', 'idiotaxon')
			;

		return $qb->getQuery()->getArrayResult();
	}

	public function getAllNotIdiotaxons()
	{
		$qb = $this->createQueryBuilder('n');

		$qb->select('n')
		   ->leftJoin('n.children', 'children')
		   ->leftJoin('children.validations', 'childrenValidations')
		   ->leftJoin('n.validations', 'validations')
		   ->addSelect('children')
		   ->addSelect('childrenValidations')
		   ->addSelect('validations')
		   ->where('n.level != :idiot')
		   ->setParameter('idiot', 'idiotaxon')
		   ;

		return $qb->getQuery()->getArrayResult();
	}
}
