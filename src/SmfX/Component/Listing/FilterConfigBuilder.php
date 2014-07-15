<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


class FilterConfigBuilder 
{

    /**
     * @var array
     */
    private $_options;

    /**
     * Construct
     *
     * @param null $options
     */
    public function __construct($options = null)
    {
        $this->_options = (is_array($options)) ? $options : array();
    }

    /**
     * Gets options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Gets form
     *
     * @return string|null
     */
    public function getForm()
    {
        return (isset($this->_options['form'])) ? $this->_options['form'] : null;
    }

    /**
     * Gets form
     *
     * @return string|null
     */
    public function getFormClass()
    {
        return (isset($this->_options['formClass'])) ? $this->_options['formClass'] : null;
    }

    /**
     * Gets page limit
     *
     * @return int|null
     */
    public function getPageLimit()
    {
        return (isset($this->_options['pageLimit'])) ? (int)$this->_options['pageLimit'] : null;
    }

    /**
     * Gets order mapping
     *
     * @return array|null
     */
    public function getOrderMapping()
    {
        return (isset($this->_options['orderMapping'])) ? $this->_options['orderMapping'] : null;
    }

    /**
     * Gets order by
     *
     * @return string|null
     */
    public function getOrderBy()
    {
        return (isset($this->_options['orderBy'])) ? $this->_options['orderBy'] : null;
    }

    /**
     * Gets order direction
     *
     * @return string|null
     */
    public function getOrderDirection()
    {
        return (isset($this->_options['orderDirection'])) ? $this->_options['orderDirection'] : null;
    }

    /**
     * Gets order direction
     *
     * @return string|null
     */
    public function getIdentifier()
    {
        return (isset($this->_options['identifier'])) ? $this->_options['identifier'] : null;
    }
} 