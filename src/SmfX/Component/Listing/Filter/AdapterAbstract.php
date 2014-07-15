<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing\Filter;


use Doctrine\ORM\Query\ResultSetMapping;
use SmfX\Component\Listing\Filter;
use SmfX\Component\Listing\FilterParameter;

abstract class AdapterAbstract implements AdapterInterface
{
    /**
     * @var array
     */
    protected $_parameters = array();

    /**
     * @var string
     */
    protected $_orderBy = null;

    /**
     * @var string
     */
    protected $_orderDirection = null;

    /**
     * @var integer
     */
    protected $_offset = null;

    /**
     * @var integer
     */
    protected $_limit = null;

    /**
     * @var string
     */
    protected $_distinction = null;

    /**
     * @var integer
     */
    protected $_totalAmount = null;

    /**
     * @var array
     */
    protected $_prepared = array(
        'where'             => array(),
        'public_parameters' => array(),
        'parameters'        => array(),
    );

    /**
     * Sets ordering
     *
     * @param string $orderBy
     * @param string $orderDirection
     * @return $this
     */
    public function setOrderBy($orderBy, $orderDirection)
    {
        $this->_orderBy          = $orderBy;
        $this->_orderDirection   = $orderDirection;
        return $this;
    }

    /**
     * Gets ordering
     *
     * @return string
     */
    public function getOrderBy()
    {
        return (!empty($this->_orderBy) && !empty($this->_orderDirection))
            ? sprintf('%s %s', $this->_orderBy, $this->_orderDirection)
            : '';
    }

    /**
     *Sets limit
     *
     * @param integer $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * Gets limit
     *
     * @return integer
     */
    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * Sets offset
     *
     * @param integer $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->_offset = $offset;
        return $this;
    }

    /**
     * Gets offset
     *
     * @return integer
     */
    public function getOffset()
    {
        return $this->_offset;
    }

    /**
     * Sets total amount of query result
     *
     * @param integer $totalAmount
     * @return $this
     */
    public function setTotalQueryResult($totalAmount)
    {
        $this->_totalAmount = $totalAmount;
        return $this;
    }

    /**
     * Gets total amount of query result
     *
     * @return integer
     */
    public function getTotalQueryResult()
    {
        return $this->_totalAmount;
    }

    /**
     * Sets ResultMapping
     *
     * @param $resultMapping
     * @return $this
     */
    public function setResultMapping($resultMapping)
    {
        throw new \Exception('setResultMapping - remove it to Doctrine adapter!');
    }

    /**
     * Gets ResultMapping
     *
     * @return array
     */
    public function getResultMapping()
    {
        throw new \Exception('setResultMapping - remove it to Doctrine adapter!');
    }

    /**
     * Sets count distinction
     *
     * @param string $distinction
     * @return $this
     */
    public function setCountDistinction($distinction)
    {
        $this->_distinction = $distinction;
        return $this;
    }

    /**
     * Gets count distinction
     *
     * @return string
     */
    public function getCountDistinction()
    {
        return $this->_distinction;
    }

    /**
     * Helper method! Counts total rows
     *
     * @param mixed $query
     * @return $this
     */
    abstract public function executeTotalQueryResult($query);

    /**
     * Main method. Returns data for listing
     *
     * @param mixed $query
     * @param string $hydrate [optional]
     * @return array
     */
    abstract public function executePaginateQueryResult($query, $hydrate = null);

    /**
     * Sets filter params
     *
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        if (!empty($params)) {
            foreach ($params as $param) {
                $this->setParam($param);
            }
        }
        return $this;
    }

    /**
     * Gets filter params
     *
     * @return FilterParameter[]
     */
    public function getParams()
    {
        return $this->_parameters;
    }

    /**
     * Sets filter values
     *
     * @param array $values
     * @return mixed
     */
    public function setValues(array $values)
    {
        if (!empty($values)) {
            foreach ($values as $name => $value) {
                if (null !== ($param = $this->getParam($name))) {
                    $param->setValue($value);
                }
            }
        }
        return $this;
    }

    /**
     * Get filter values
     *
     * @return mixed
     */
    public function getValues()
    {
        $this->getPublicQueryParameters();
    }

    /**
     * Creates filter param
     *
     * @param string $name
     * @param mixed $value
     * @param string $condition
     * @param array $spec
     * @return $this
     */
    public function createParam($name, $value, $condition = null, $spec = array())
    {
        $this->_parameters[$name] = new FilterParameter($name, $value, $condition, $spec);
        return $this;
    }

    /**
     * Sets filter param
     *
     * @param FilterParameter $param
     * @return $this
     */
    public function setParam(FilterParameter $param)
    {
        $this->_parameters[$param->getName()] = $param;
        return $this;
    }

    /**
     * Gets filter param
     *
     * @param string $name
     * @return FilterParameter|null
     */
    public function getParam($name)
    {
        if (array_key_exists($name, $this->_parameters)) {
            return $this->_parameters[$name];
        }
    }

    /**
     * Gets query parameters
     *
     * @param bool $public [optional] - false(default): - returns all parameters
     *                                  true:           - returns without "hidden"
     * @param mixed $queryBuilder [optional]
     * @return mixed
     */
    public function getQueryParameters($public = false, $queryBuilder = null)
    {
        $this->_prepareParams();

        $index = ($public) ? 'public_parameters' : 'parameters';
        if (is_null($queryBuilder)) {
            return isset($this->_prepared[$index]) ? $this->_prepared[$index] : array();
        } else {
            foreach ($this->_prepared[$index] as $name => $param) {
                $queryBuilder->setParameter($name, $param);
            }
            return $queryBuilder;
        }
    }

    /**
     * Alias self::getQueryParameters(true)
     *
     * @return mixed
     */
    public function getPublicQueryParameters()
    {
        return $this->getQueryParameters(true);
    }

    /**
     * Gets query where
     *
     * @param mixed $queryBuilder [optional]
     * @return mixed
     */
    public function getQueryWhere($queryBuilder = null)
    {
        $this->_prepareParams();

        if (is_null($queryBuilder)) {
            return $this->_prepared['where'];
        } else {
            foreach ($this->_prepared['where'] as $name => $where) {
                $queryBuilder->andWhere($where);
            }
            return $queryBuilder;
        }
    }

    /**
     * Metoda przygotowuje dane do zapytania
     *
     * @return array
     */
    private function _prepareParams()
    {
        $params = $this->getParams();
        foreach ($params as $param) {
            $name  = $param->getName();
            $value = $param->getDqlValue(); //todo: injection
            if ($param->isEmpty($value) && !$param->isRestricted()) {
                if (array_key_exists($name, $this->_prepared['where'])) {
                    unset($this->_prepared['where'][$name]);
                }
                if (array_key_exists($name, $this->_prepared['parameters'])) {
                    unset($this->_prepared['parameters'][$name]);
                }
                if (array_key_exists($name, $this->_prepared['public_parameters'])) {
                    unset($this->_prepared['public_parameters'][$name]);
                }
                continue;
            }

            if (!$param->isMock()) {
                $this->_prepared['where'][$name] = $param->getDqlCondition(); //todo: injection
                $this->_prepared['parameters'][$name] = $value;
            }
            if (!$param->isHidden()) {
                $unfilteredValue = $param->getUnfilteredValue();
                if ($value instanceof \DateTime) {
                    $unfilteredValue = $value
                        ->format(($value->format('H') != 0 || $value->format('i') != 0 || $value->format('m') != 0)
                            ? Filter::$defaultDateTimeFormat : Filter::$defaultDateFormat);
                }
                $this->_prepared['public_parameters'][$name] = $unfilteredValue;
            }
        }
    }

} 