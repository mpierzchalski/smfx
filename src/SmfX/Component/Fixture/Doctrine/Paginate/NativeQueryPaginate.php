<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Fixture\Doctrine\Paginate;


use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query;
use SmfX\Component\Fixture\Doctrine\NativeQuery as FixtureNativeQuery;

class NativeQueryPaginate
{
    /**
     * @param NativeQuery $query
     * @param string $distinction [optional]
     * @return int
     */
    static public function count(NativeQuery $query, $distinction = '')
    {
        return self::createCountQuery($query);
    }

    /**
     * @param NativeQuery $query
     * @param string $distinction [optional]
     * @return int
     */
    static public function getTotalQueryResults(NativeQuery $query, $distinction = '')
    {
        return self::createCountQuery($query, $distinction);
    }

    /**
     * @param NativeQuery $query
     * @param string $distinction [optional]
     * @return array
     */
    static public function createCountQuery(NativeQuery $query, $distinction = '')
    {
        $sql   = $query->getSQL();
        $count = (!empty($distinction)) ? 'DISTINCT ' . $distinction : '1';
        $sql = preg_replace(
            '/^SELECT(.*?)FROM/i', 'SELECT COUNT(' . $count . ') as count_result FROM',
            preg_replace('/\n/i', '', $sql)
        );
        $sql = preg_replace('/ORDER BY(.[^\)]*)$/i', '', $sql);

        $rsm = new Query\ResultSetMapping();
        $rsm->addScalarResult('count_result', 'count_result');

        return FixtureNativeQuery::doExecute($query->setSQL($sql), Query::HYDRATE_SINGLE_SCALAR, $rsm);
    }

    /**
     * @param NativeQuery $query
     * @param integer $limit
     * @param integer $offset
     * @return NativeQuery
     */
    static public function getLimitedQuery(NativeQuery $query, $limit, $offset)
    {
        $sql = $query->getSQL();
        $sql .= " LIMIT " . $limit . " OFFSET " . $offset;
        $query->setSQL($sql);
        return $query;
    }
} 