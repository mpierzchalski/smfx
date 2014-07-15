<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


class FilterParameter
{
    /**
     * @var string
     */
    protected $_name = null;

    /**
     * @var array
     */
    protected $_config = null;

    /**
     * @var mixed
     */
    protected $_value = null;

    /**
     * @var string
     */
    protected $_comparisonOperator = null;

    /**
     * @var string
     */
    protected $_expression = null;

    /**
     * @var string
     */
    protected $_mapping = null;

    /**
     * @var boolean
     */
    protected $_mock = null;

    /**
     * @var array
     */
    protected $_filters = null;

    /**
     * @var string
     */
    protected $_outputType = null;

    /**
     * @var boolean
     */
    protected $_readonly = false;

    /**
     * @var boolean
     */
    protected $_hidden = false;

    /**
     * @var mixed
     */
    protected $_emptyValue = null;

    /**
     * @var mixed
     */
    protected $_restriction = null;

    /**
     * Construct
     *
     * @param string $name
     * @param mixed $value
     * @param string $condition[optional]
     * @param array $spec[optional]
     */
    public function __construct($name, $value, $condition = null, $spec = array())
    {
        $this->_name  = $name;
        $this->_value = $value;
        $this->setComparisonOperator($condition);

        $this->_config = array_merge(array(
            'mock'          => $this->_mock,
            'filters'       => $this->_filters,
            'mapping'       => $this->_mapping,
            'expression'    => $this->_expression,
            'outputType'    => $this->_outputType,
            'readonly'      => $this->_readonly,
            'hidden'        => $this->_hidden,
            'emptyValue'    => $this->_emptyValue,
            'restriction'   => $this->_restriction,
        ), $spec);
        if (null !== ($mock = $this->_config['mock'])) {
            $this->_mock = (bool)$mock;
        }
        if (null !== ($filters = $this->_config['filters'])) {
            $this->_filters = $filters;
        }
        if (null !== ($mapping = $this->_config['mapping'])) {
            $this->_mapping = (string)$mapping;
        }
        if (null !== ($expression = $this->_config['expression'])) {
            $this->_expression = (string)$expression;
        }
        if (null !== ($outputType = $this->_config['outputType'])) {
            $this->_outputType = (string)$outputType;
        }
        if (null !== ($readonly = $this->_config['readonly'])) {
            $this->_readonly = (bool)$readonly;
        }
        if (null !== ($hidden = $this->_config['hidden'])) {
            $this->_hidden = (bool)$hidden;
        }
        if (null !== ($emptyValue = $this->_config['emptyValue'])) {
            $this->_emptyValue = $emptyValue;
        }
        if (null !== ($restriction = $this->_config['restriction'])) {
            $this->_restriction = $restriction;
        }
    }

    /**
     * Gets parameter name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Gets parameter value [filtered]
     *
     * @return mixed
     */
    public function getValue()
    {
        if (!$this->isEmpty($this->_value)) {
            $filters = $this->getFilters();
            if (!empty($filters)) {
                $value = $this->_value;
                foreach ($filters as $filter) { //todo: filtersCollection
                    $value = $filter->filter($value);
                }
                return $value;
            }
        }
        return $this->_value;
    }

    /**
     * Gets unfiltered value
     *
     * @return mixed
     */
    public function getUnfilteredValue()
    {
        return $this->_value;
    }

    /**
     * Sets value
     *
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        if (!$this->isReadonly()) {
            $this->_value = $value;
        }
        return $this;
    }

    /**
     * Clear parameter value
     *
     * @return $this
     */
    public function clearValue()
    {
        $this->setValue($this->_emptyValue);
        return $this;
    }

    /**
     * Gets mapping
     *
     * @return string
     */
    public function getMapping()
    {
        return $this->_mapping;
    }

    /**
     * Gets filters collection
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->_filters;
    }

    /**
     * Is parameter readonly?
     * It prevents by overwriting value from request query.
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return (bool)$this->_readonly;
    }

    /**
     * Is parameter hidden?
     * It hides parameter in urls and public uses.
     *
     * @return boolean
     */
    public function isHidden()
    {
        return (bool)$this->_hidden;
    }

    /**
     * Gets comparison operator
     *
     * @return string
     */
    public function getComparisonOperator()
    {
        return $this->_comparisonOperator;
    }

    /**
     * Sets comparison operator
     *
     * @param string $comparisonOperator
     * @return $this
     */
    public function setComparisonOperator($comparisonOperator)
    {
        $this->_comparisonOperator = (null !== $comparisonOperator) ? $comparisonOperator : '=';
        return $this;
    }

    /**
     * Checks if value is empty
     *
     * @param mixed $value
     * @return boolean
     */
    public function isEmpty($value)
    {
        if ($this->_outputType == T_ARRAY && !is_array($value)) {
            $toCompare = reset($this->_emptyValue);
            return $value == $toCompare;
        }
        return (is_string($this->_emptyValue) && !is_object($value) && !is_array($value))
            ? (string)$value == $this->_emptyValue
            : $value == $this->_emptyValue;
    }

    /**
     * Is restricted?
     * If list must be narrowed down to list contains certain rows.
     * For example if you want to list users from certain group as default and filter cannot change it anyways.
     *
     * @return boolean
     */
    public function isRestricted()
    {
        return !empty($this->_restriction);
    }

    /**
     * Is mock?
     *
     * @return boolean
     */
    public function isMock()
    {
        return $this->_mock;
    }

    /**
     * Gets condition
     *
     * @return string
     */
    public function getDqlCondition()
    {
        $value = $this->getDqlValue();
        if ((is_array($value) && !empty($value)) || (empty($value) && $this->isRestricted())) {
            return $this->_mapping . ' ' . (($this->_comparisonOperator) ? 'IN' : 'NOT IN') . ' (:' . $this->_name . ')';
        }
        if ($this->_comparisonOperator == 'LIKE' && $value != $this->_emptyValue) {
            $this->_mapping = sprintf('upper(%s)', $this->_mapping);
        }
        return $this->_mapping . ' ' . $this->_comparisonOperator . ' :' . $this->_name;
    }

    /**
     * Gets value
     *
     * @return mixed
     */
    public function getDqlValue()
    {
        $value = $this->getValue();
        if (strtolower($this->_outputType) == 'datetime' && $value != $this->_emptyValue) {
            try {
                return (!$value instanceof \DateTime) ? new \DateTime($value) : $value;
            } catch (\Exception $e) {
                return null;
            }
        }
        if ($this->_comparisonOperator == 'LIKE' && $value != $this->_emptyValue) {
            $value = !empty($this->_expression)
                ? preg_replace('/%value%/i', mb_strtoupper($value), $this->_expression)
                : '%' . mb_strtoupper($value) . '%' ;
        }
        if (empty($value) && $this->isRestricted()) {
            return is_array($this->_restriction) ? $this->_restriction : $value;
        }
        return $value;
    }
} 