<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


use SmfX\Component\Collection\FilteredCollection;
use SmfX\Component\Listing\Exceptions\Listing\FilterBuilderException;
use SmfX\Component\Listing\Exceptions\Listing\StackLoaderException;
use SmfX\Component\Listing\Filter\AdapterInterface as FilterAdapterInterface;
use Symfony\Component\HttpFoundation\Request;

class Filter
{

    /**
     * Default rows page limit
     */
    const DEFAULT_LIMIT = 10;

    /**
     * Default ordering direction
     */
    const DEFAULT_ORDER_DIRECTION = 'DESC';

    /**
     * Default order by param
     */
    const DEFAULT_ORDER_BY = 'id';

    /**
     * Default identifier
     */
    const DEFAULT_IDENTIFIER = 'id';

    /**
     * @var string
     */
    static public $defaultDateTimeFormat = 'd-m-Y H:i:s';

    /**
     * @var string
     */
    static public $defaultDateFormat = 'd-m-Y';

    /**
     * @var FilteredCollection
     */
    private $_collection;

    /**
     * @var FilterConfigBuilder
     */
    private $_config;

    /**
     * @var FilterForm
     */
    private $_form;

    /**
     * @var array
     */
    private $_arguments;

    /**
     * @var Input
     */
    private $_input;

    /**
     * Limit of rows per page
     *
     * @var integer
     */
    protected $_pageLimit;

    /**
     * Page number
     *
     * @var integer
     */
    protected $_pageNo = 1;

    /**
     * Ordering property
     *
     * @var string
     */
    protected $_orderBy;

    /**
     * Ordering direction
     *
     * @var (string) ASC|DESC
     */
    protected $_orderDirection;

    /**
     * Mapping for ordering properties
     * @example: array('column_name' => 'alias')
     *
     * @var array
     */
    protected $_orderByMapping = [];

    /**
     * Identifier param's name
     *
     * @var string
     */
    protected $_identifier;

    /**
     * Does this listing have to be paginated
     *
     * @var boolean
     */
    protected $_pagination = true;

    /**
     * Constructor
     *
     * @param FilteredCollection  $collection
     * @param FilterConfigBuilder $config
     * @param FilterForm          $form [optional]
     */
    public function __construct(FilteredCollection $collection, FilterConfigBuilder $config, FilterForm $form = null)
    {
        $this->_collection = $collection;
        $this->_config     = $config;
        $this->_form       = $form;

        $this->_collection->setConfig($config->getOptions());
        $this->setDefaultPageLimit();
        $this->setOrderMapping();
        $this->setDefaultOrderBy();
        $this->setDefaultOrderDirection();
        $this->setDefaultIdentifier();
    }

    /**
     * Creates filter parameter
     *
     * @param string    $name
     * @param mixed     $value
     * @param string    $condition
     * @param array     $spec
     * @return $this
     */
    public function createParameter($name, $value, $condition = null, $spec = array())
    {
        //todo: parameter builder..
        $this->getCollection()->getFilter()->createParam($name, $value, $condition, $spec);
        return $this;
    }

    /**
     * Handles request data
     *
     * @param Input $input
     * @return $this
     */
    public function handleInput(Input $input)
    {
        $this->_input = $input;
        if ($this->_form) {
            $this->_form->handleInput($input);
        }
        return $this;
    }

    /**
     * Gets form
     *
     * @return FilterForm
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * Sets arguments for searching
     *
     * @param array $arguments
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->_arguments = $arguments;
        return $this;
    }

    /**
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier($identifier)
    {
        $this->_identifier = $identifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * @param int $pageLimit
     * @return $this
     */
    public function setPageLimit($pageLimit)
    {
        $this->_pageLimit = $pageLimit;
        $this
            ->getCollection()
            ->setLimit($pageLimit)
                ->getFilter()
                ->setLimit($pageLimit);
        return $this;
    }

    /**
     * @return int
     */
    public function getPageLimit()
    {
        return $this->_pageLimit;
    }

    /**
     * @param int $pageNo
     * @return $this
     */
    public function setPageNo($pageNo)
    {
        $this->_pageNo = $pageNo;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageNo()
    {
        return $this->_pageNo;
    }

    /**
     * @param boolean $pagination
     * @return $this
     */
    public function setPagination($pagination)
    {
        $this->_pagination = $pagination;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getPagination()
    {
        return $this->_pagination;
    }

    /**
     * @param string $orderBy
     * @return $this
     * @throws FilterBuilderException
     */
    public function setOrderBy($orderBy)
    {
        $orderMapping = $this->getOrderByMapping();
        if (!array_key_exists($orderBy, $orderMapping)) {
            throw new FilterBuilderException(
                'OrderBy(' . $orderBy . ') can be set only if exists in orderMapping configuration!'
            );
        }
        $this->_orderBy = $orderBy;
        $this->getCollection()->getFilter()->setOrderBy($orderBy, $this->getOrderDirection());
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderBy()
    {
        return $this->_orderBy;
    }

    /**
     * @param array $orderByMapping
     * @return $this
     */
    public function setOrderByMapping($orderByMapping)
    {
        $this->_orderByMapping = $orderByMapping;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrderByMapping()
    {
        return $this->_orderByMapping;
    }

    /**
     * @param mixed $orderDirection
     * @return $this
     */
    public function setOrderDirection($orderDirection)
    {
        $this->_orderDirection = $orderDirection;
        $this->getCollection()->getFilter()->setOrderBy($this->getOrderBy(), $orderDirection);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderDirection()
    {
        return $this->_orderDirection;
    }

    /**
     * @return FilteredCollection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Validates filter
     */
    public function isValid()
    {
        if ($this->_form) {
            return $this->_form->isValid();
        }
        return true;
    }

    /**
     * Main method which returns filtered data from adapter
     *
     * @param string $methodName - method's name caught by __call() in Listing service
     * @return FilteredCollection
     * @throws StackLoaderException
     */
    public function loadStack($methodName)
    {
        $isFilter      = false;
        $_arguments    = array();

        if (!empty($this->_arguments)) {
            $_arguments = $this->_arguments;
            foreach ($_arguments as $argument) {
                if ($argument instanceof FilterAdapterInterface) {
                    $isFilter = true;
                }
            }
        }

        $filterAdapter = $this->getCollection()->getFilter();
        $_outputFormat = $this->_input->getOutputFormat();
        if ($this->_form) {
            $filterAdapter->setParams($this->_form->getParameters());
        }

        if (false === $isFilter || empty($_arguments)) {
            if (!empty($this->_orderByMapping) && array_key_exists($this->getOrderBy(), $this->_orderByMapping)) {
                $filterAdapter->setOrderBy($this->_orderByMapping[$this->getOrderBy()], $this->getOrderDirection());
            }

            if (empty($_outputFormat)) {
                $filterAdapter
                    ->setLimit($this->getPageLimit())
                    ->setOffset($this->getPageLimit()*($this->getPageNo()-1));
            }
            $_arguments[] = $filterAdapter;
        }

        try {
            $this->getCollection()->setFilterParams($_arguments);
            if (!empty($_outputFormat)) {
                $this->getCollection()->setMode(FilteredCollection::MODE_FILTER_OFF);
            }
            return $this->getCollection();

        } catch (\Exception $e) {
            throw new StackLoaderException('Some error has been occurred during stack loading.', 0, $e);
        }
    }

    /**
     * Sets default amount of rows per page
     * WARNING! Only [5, 10, 20, 50] are allowed
     *
     * @param integer $pageLimit [optional]
     * @return $this
     */
    public function setDefaultPageLimit($pageLimit = null)
    {
        if (null !== $pageLimit && !array_key_exists($pageLimit, self::limiterOptions())) {
            $pageLimit = null;
        }
        if (empty($pageLimit)) {
            $pageLimit = $this->_config->getPageLimit();
        }
        if (null === $pageLimit) {
            $pageLimit = self::DEFAULT_LIMIT;
        }
        $this->setPageLimit($pageLimit);
        return $this;
    }

    /**
     * Sets mapping for ordering params
     *
     * IMPORTANT!! System are going to order by first defined param in stack
     * @example:
     * $mapping = array(
     *      'id' => 'd.id'
     * );
     *
     * @param array $mapping
     *
     * @return $this
     */
    public function setOrderMapping($mapping = null)
    {
        if (null === $mapping) {
            $mapping = $this->_config->getOrderMapping();
        }
        if (is_array($mapping)) {
            $this->setOrderByMapping($mapping);
            $mapping = array_keys($mapping);
            $mapping = reset($mapping);
            $this->setDefaultOrderBy($mapping);
        }
        return $this;
    }

    /**
     * Sets default order by
     *
     * @param string $orderBy
     * @return $this
     * @throws FilterBuilderException
     */
    public function setDefaultOrderBy($orderBy = null)
    {
        if (null === $orderBy) {
            $orderBy = $this->_config->getOrderBy();
            if (empty($orderBy)) {
                $orderBy = self::DEFAULT_ORDER_BY;
            }
        } else {
            if (is_array($this->_orderByMapping)
                && !empty($this->_orderByMapping)
                && !array_key_exists($orderBy, $this->_orderByMapping)
            ) {
                throw new FilterBuilderException("There is no default orderBy param in orderByMapping stack.");
            }
        }
        $this->setOrderBy($orderBy);
        return $this;
    }

    /**
     * Sets default ordering direction
     *
     * @param string $orderDirection - ASC|DESC
     * @return $this
     */
    public function setDefaultOrderDirection($orderDirection = null)
    {
        if (null === $orderDirection) {
            $orderDirection = $this->_config->getOrderDirection();
        }
        if (null === $orderDirection) {
            $orderDirection = self::DEFAULT_ORDER_DIRECTION;
        }
        $this->setOrderDirection($orderDirection);
        return $this;
    }

    /**
     * Sets default identifier
     *
     * @param string $identifier[optional] - identifier
     * @return $this
     */
    public function setDefaultIdentifier($identifier = null)
    {
        if (null !== $identifier) {
            $identifier = $this->_config->getIdentifier();
        }
        if (null === $identifier) {
            $identifier = self::DEFAULT_IDENTIFIER;
        }
        $this->setIdentifier($identifier);
        return $this;
    }

    /**
     * Limiter options
     *
     * @return array
     */
    static public function limiterOptions()
    {
        return array(
            5  => '5',
            10 => '10',
            20 => '20',
            50 => '50'
        );
    }
} 