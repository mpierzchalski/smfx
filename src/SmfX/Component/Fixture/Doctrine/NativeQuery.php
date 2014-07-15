<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Fixture\Doctrine;

use Doctrine\ORM\Query\ResultSetMapping;

final class NativeQuery
{
    /**
     * Invokes native query
     *
     * @param \Doctrine\ORM\NativeQuery $query
     * @param $hydrator
     * @param \Doctrine\ORM\Query\ResultSetMapping $rsm
     * @return mixed
     */
    static function doExecute(\Doctrine\ORM\NativeQuery $query, $hydrator, ResultSetMapping $rsm)
    {
        $em         = $query->getEntityManager();
        $sql        = $query->getSQL();
        $parameters = $query->getParameters();

        $_conn  = $em->getConnection();
        $handle = $_conn->prepare($sql);
        $handle->execute($parameters);

        $objectHydrator = $em->newHydrator($hydrator);
        return $objectHydrator->hydrateAll($handle, $rsm);
    }
}