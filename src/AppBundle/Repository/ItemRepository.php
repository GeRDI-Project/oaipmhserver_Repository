<?php
/**
 * This file is part of the GeRDI software suite
 *
 * @author  Tobias Weber <weber@lrz.de>
 * @license https://www.apache.org/licenses/LICENSE-2.0
 */
namespace AppBundle\Repository;

/**
 * ItemRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ItemRepository extends \Doctrine\ORM\EntityRepository
{
	public function getNitems(String $offset, String $limit)
    {
    	$query='SELECT u FROM AppBundle:Item u  LIMIT '.$limit.' OFFSET '.$offset;
    	print($query);
        return $this->_em->createQuery($query)
                         ->getResult();
    }
}
