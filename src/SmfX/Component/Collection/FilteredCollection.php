<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Collection;


use SmfX\Component\Listing\Filter\AdapterInterface as FilterAdapterInterface;

class FilteredCollection extends CollectionAbstract
{
    /**
     * Default limit
     */
    const LIMIT = 10;

    /**
     * Default mode
     */
    const MODE_FILTER_ON  = 'on';

    /**
     * It loads data within limit
     */
    const MODE_FILTER_OFF = 'off';

    /**
     * @var array
     */
    protected $_filter = null;

    /**
     * @var array
     */
    protected $_filterParams = array();

    /**
     * @var array
     */
    protected $_config = null;

    /**
     * @var boolean
     */
    protected $_partials = false;

    /**
     * @var integer
     */
    protected $_partNo = 0;

    /**
     * @var integer
     */
    protected $_totalCounts = 0;

    /**
     * @var integer
     */
    protected $_partsAmount = 0;

    /**
     * @var string
     */
    protected $_mode = self::MODE_FILTER_ON;

    /**
     * @var integer
     */
    protected $_limit = self::LIMIT;

    /**
     * Constructor overwrites parent construct in order to provide filter adapter.
     *
     * @param FilterAdapterInterface $filter
     * @param array $elements
     */
    public function __construct(FilterAdapterInterface $filter, array $elements = array())
    {
        $this->_filter = $filter;
        parent::__construct($elements);
    }

    /**
     * @param array $config
     * @return $this
     */
    public function setConfig($config)
    {
        $this->_config = $config;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * {@inheritdoc}
     */
    public function & toArray()
    {
        if (empty($this->_elements)) {
            $this->load();
        }
        return $this->_elements;
    }

    /**
     * Sets limit
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
     * Sets part number
     *
     * @param integer $partNo
     * @return $this
     */
    public function setPartNo($partNo)
    {
        $this->_partNo = $partNo;
        return $this;
    }

    /**
     * Sets mode, compatible with self::MODE_*
     *
     * @param string $mode
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setMode($mode)
    {
        switch ($mode) {
            case self::MODE_FILTER_ON:  break;
            case self::MODE_FILTER_OFF: break;
            default:
                throw new \InvalidArgumentException('Unsupported mode: ' . $mode);
        }
        $this->_mode = $mode;
        return $this;
    }

    /**
     * Sets filter params and propagate them to filter adapter
     *
     * @param array $filterParams
     */
    public function setFilterParams(array $filterParams)
    {
        $this->_filterParams = $filterParams;
        $this->getFilter()->setParams($filterParams);
    }

    /**
     * Loads data
     *
     * @return array|null
     */
    public function & load()
    {
        if (!empty($this->_elements)) {
            $this->clear();
        }

        $filter = $this->getFilter();
        if ($this->_mode == self::MODE_FILTER_ON) {
            if ($filter instanceof FilterAdapterInterface) {
                $filter->setOffset($this->_partNo * $this->_limit);
                $filter->setLimit($this->_limit);

                if (!$this->_partials) {
                    $this->_partNo++;
                }
            }
        }
        $_elements = $this->slice(null);
        if ($this->_mode == self::MODE_FILTER_ON) {
            if ($filter instanceof FilterAdapterInterface) {
                $this->_totalCounts = $filter->getTotalQueryResult();
                if ($this->_totalCounts > 0) {
                    $this->_partsAmount = ceil($this->_totalCounts/$this->_limit);
                }
            }
        }
        if (empty($_elements)) {
            $this->clear();
        }
        $this->_elements = $_elements;
        return $this->_elements;
    }

    /**
     * Loads particular part of data
     *
     * @param integer $partNo[options]
     * @return array|null
     */
    public function & loadPart($partNo = 0)
    {
        $this->_partNo   = $partNo;
        $this->_partials = true;
        return $this->load();
    }

    /**
     * Gets current part number
     *
     * @return int
     */
    public function getCurrentPart()
    {
        return $this->_partNo;
    }

    /**
     * Gets pages amount
     *
     * @return int
     */
    public function getPagesAmount()
    {
        return $this->_partsAmount;
    }

    /**
     * Gets total quantity
     *
     * @return integer
     */
    public function getTotalQuantity()
    {
        return $this->_totalCounts;
    }

    /**
     * Gets loader limit
     *
     * @return integer
     */
    public function getLoaderLimit()
    {
        return $this->_limit;
    }

    /**
     * Gets filter adapter
     *
     * @return FilterAdapterInterface
     */
    public function getFilter()
    {
        return $this->_filter;
    }
} 