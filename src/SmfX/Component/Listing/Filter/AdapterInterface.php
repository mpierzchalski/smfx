<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing\Filter;


use SmfX\Component\Listing\FilterParameter;

interface AdapterInterface
{

    /**
     * Gets collection name. One of "array", "filtered"
     *
     * @return string
     */
    public function getCollectionName();

    /**
     * Sets ordering
     *
     * @param string $orderBy
     * @param string $orderDirection
     * @return $this
     */
    public function setOrderBy($orderBy, $orderDirection);

    /**
     * Gets ordering
     *
     * @return string
     */
    public function getOrderBy();

    /**
     *Sets limit
     *
     * @param integer $limit
     * @return $this
     */
    public function setLimit($limit);

    /**
     * Gets limit
     *
     * @return integer
     */
    public function getLimit();

    /**
     * Sets offset
     *
     * @param integer $offset
     * @return $this
     */
    public function setOffset($offset);

    /**
     * Gets offset
     *
     * @return integer
     */
    public function getOffset();

    /**
     * Sets total amount of query result
     *
     * @param integer $totalAmount
     * @return $this
     */
    public function setTotalQueryResult($totalAmount);

    /**
     * Gets total amount of query result
     *
     * @return integer
     */
    public function getTotalQueryResult();

    /**
     * @todo
     *
     * @param $resultMapping
     * @return $this
     */
    public function setResultMapping($resultMapping);

    /**
     * Sets count distinction
     *
     * @param string $distinction
     * @return $this
     */
    public function setCountDistinction($distinction);

    /**
     * Gets count distinction
     *
     * @return string
     */
    public function getCountDistinction();

    /**
     * Helper method! Counts total rows
     *
     * @param mixed $query
     * @return $this
     */
    public function executeTotalQueryResult($query);

    /**
     * Main method. Returns data for listing
     *
     * @param mixed $query
     * @param string $hydrate [optional]
     * @return array
     */
    public function executePaginateQueryResult($query, $hydrate = null);

    /**
     * Sets filter params
     *
     * @param array $params
     * @return $this
     */
    public function setParams(array $params);

    /**
     * Gets filter params
     *
     * @return FilterParameter[]
     */
    public function getParams();

    /**
     * Sets filter values
     *
     * @param array $values
     * @return mixed
     */
    public function setValues(array $values);

    /**
     * Get filter values
     *
     * @return mixed
     */
    public function getValues();

    /**
     * Creates filter param
     *
     * @param string    $name
     * @param mixed     $value
     * @param string    $condition
     * @param array     $spec
     * @return $this
     */
    public function createParam($name, $value, $condition = null, $spec = array());

    /**
     * Sets filter param
     *
     * @param FilterParameter $param
     * @return $this
     */
    public function setParam(FilterParameter $param);

    /**
     * Gets filter param
     *
     * @param string $name
     * @return FilterParameter|null
     */
    public function getParam($name);

    /**
     * Gets query parameters
     *
     * @param bool $public [optional] - false(default): - returns all parameters
     *                                  true:           - returns without "hidden"
     * @param mixed $queryBuilder [optional]
     * @return mixed
     */
    public function getQueryParameters($public = false, $queryBuilder = null);

    /**
     * Alias self::getQueryParameters(true)
     *
     * @return mixed
     */
    public function getPublicQueryParameters();

    /**
     * Gets query where
     *
     * @param mixed $queryBuilder [optional]
     * @return mixed
     */
    public function getQueryWhere($queryBuilder = null);

}