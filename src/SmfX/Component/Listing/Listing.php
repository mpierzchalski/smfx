<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;
use SmfX\Component\Collection\ArrayCollection;
use SmfX\Component\Collection\FilteredCollection;
use SmfX\Component\Listing\Exceptions\Listing\NoFilterException;
use SmfX\Component\Listing\Exceptions\Listing\NoResultException;
use SmfX\Component\Listing\Exceptions\Listing\StackLoaderException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method void findAll() findAll() main method launching list
 */
class Listing
{

    /**
     * Listing's mode
     * Full mode gives access to all features for a developer
     */
    const MODE_FULL = 'full';

    /**
     * Listing's mode
     * Read-only limits access for some features and protects loaded data against overwritten
     * This mode is depended on called listing's method
     */
    const MODE_READONLY = 'read-only';

    /**
     * @var string
     */
    private $_mode = self::MODE_READONLY;

    /**
     * @var string
     */
    private $_name = '';

    /**
     * @var array
     */
    private $_config = array();

    /**
     * @var StorageInterface
     */
    private $_storage;

    /**
     * @var object
     */
    private $_service;

    /**
     * @var Filter
     */
    private $_filter;

    /**
     * @var Input
     */
    private $_input;

    /**
     * @var FilteredCollection
     */
    private $_stackElements;

    /**
     * @var ArrayCollection
     */
    private $_stackElementsKeys;

    /**
     * @var ListingView
     */
    private $_view;

    /**
     * @var string
     */
    private $_oneLoadMethod = '';

    /**
     * @var string
     */
    private $_listSaveMethod = '';

    /**
     * @var mixed
     */
    private $_pickedElement;

    /**
     * @var boolean
     */
    private $_initialized = false;

    /**
     * @var bool
     */
    private $_saved = false;

    /**
     * Constructor
     *
     * @param string $name
     * @param array $config
     */
    public function __construct($name, array $config)
    {
        $this->_name   = $name;
        $this->_config = $config;
    }

    /**
     * Sets storage instance
     *
     * @param StorageInterface $storage
     * @return $this
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->_storage = $storage;
        return $this;
    }

    /**
     * Sets service instance
     *
     * @param object $service
     * @return $this
     */
    public function setService($service)
    {
        $this->_service = $service;
        return $this;
    }

    /**
     * Sets filter instance
     *
     * @param Filter $filter
     * @return $this
     */
    public function setFilter(Filter $filter)
    {
        $this->_filter = $filter;
        return $this;
    }

    /**
     * Gets filter
     *
     * @return Filter
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * Sets input
     *
     * @param Input $input
     * @return $this
     */
    public function setInput(Input $input)
    {
        $this->_input = $input;
        return $this;
    }

    /**
     * Register listing in container storage
     *
     * @param ListingContainer $container
     * @return $this
     */
    public function register(ListingContainer $container)
    {
        $container->add($this);
        return $this;
    }

    /**
     * Register view in listing instance
     *
     * @param ListingView $view
     * @return $this
     */
    public function registerView(ListingView $view)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Magic method retrieve data from filter
     *
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $this->_initialize();
        switch (true) {

            // Example: findOneById - search by Id which value is gotten from request query param "id".
            case (preg_match('/^findOneBy/', $name) && $this->_mode == self::MODE_READONLY):
                return $this->_oneLoad($name);
                break;

            // Example: findOneActivePatientByInsuranceNumber($insuranceNumber)
            case (preg_match('/^findOne/', $name) && $this->_mode == self::MODE_READONLY):
                return $this->_oneLoad($name, $arguments, true);
                break;

            // Example: findByFilter, findActiveByFilter, etc..
            // In this convention service retrieves you founded rows by given filter.
            case (preg_match('/^find/', $name)):
                $this->_mode = self::MODE_FULL;

                return $this->_listLoad($name, $arguments);
                break;

            // Example: saveList, saveCheckedRows, removeCheckedRows
            // You can call service's methods in this way.
            default :
                $this->_mode = self::MODE_FULL;

                $this->_listSaveMethod = $name;
                return $this->_listSave();
                break;
        }
    }

    /**
     * Method initialize listing
     *
     * @return void
     * @throws \RuntimeException
     */
    private function _initialize()
    {
        if (!$this->_initialized) {
            //Look up into storage
            if (!$this->_storage->isEmpty()) {
                $snapshot = $this->_storage->read();

                if ($this->getName() !== $snapshot->getName()) {
                    throw new \RuntimeException(sprintf(
                        'Listing name violation! Stored data(%s) has different name than initialized(%s).',
                        $snapshot->getName(),
                        $this->getName()
                    ));
                }

                // Sets stack elements keys
                $this->_stackElementsKeys = new ArrayCollection($snapshot->getIdentifiers());

                // Sets data to form if exists
                $form = $this->getFilter()->getForm();
                if ($form instanceof FilterForm) {
                    $form->setData($snapshot->getFilter());
                }
            }
            $this->_initialized = true;
        }
    }

    /**
     * Loads data from stack.
     *
     * @param string $name
     * @return mixed
     * @throws NoResultException
     */
    protected function _oneLoad($name = null)
    {
        $paramName = null;
        if (null !== $name) {
            $this->_oneLoadMethod = $name;
            $paramName = strtolower(substr($name, strlen('findOneBy')));
            if (empty($paramName)) {
                throw new NoResultException('Item not found.');
            }
        }
        return $this->pickRow($this->_input->get($paramName));
    }

    /**
     * Calls service's methods and decorate data in collector object
     *
     * @param string $name
     * @param array $arguments
     * @return ListingResult
     * @throws NoFilterException
     * @throws StackLoaderException
     */
    protected function _listLoad($name, $arguments)
    {
        if (!$filter = $this->getFilter()) {
            throw new NoFilterException("You must define filter object before for using {$name} listing!");
        }
        // Handling input
        $this->getFilter()->handleInput($this->_input);
        if (!$filter->isValid()) {
            $this->getFilter()->getCollection()->clear();
        } else {
            $items = $filter
                ->setArguments($arguments)
                ->loadStack($name);

            if (!$items instanceof FilteredCollection) {
                throw new StackLoaderException("Some errors have been occurred during reading data!");
            }
            $this->setStackRows($items);
        }
        return $this;
    }

    /**
     * Saves list form
     *
     * @throws \RuntimeException
     */
    protected function _listSave()
    {
        throw new \RuntimeException('Method not implemented yet!');
    }

    /**
     * Saves list snapshot to storage
     */
    protected function _saveData()
    {
        if (!$this->_saved) {
            $this->_saved = true;
            $this->_storage->write(new ListingSnapshot($this));
        }
    }

    /**
     * Creates View object
     *
     * @return ListingView
     */
    public function createView()
    {
        $this->_saveData();
        return $this->_view;
    }

    /**
     *  Gets list result
     *
     * @return FilteredCollection
     */
    public function getStackRows()
    {
        return $this->_stackElements;
    }

    /**
     * Gets identifiers result rows
     *
     * @return ArrayCollection|null
     */
    public function getStackRowsIdentifiers()
    {
        return $this->_stackElementsKeys;
    }

    /**
     * Sets list result
     *
     * @param FilteredCollection $items [optional]
     * @return $this
     */
    public function setStackRows(FilteredCollection $items = null)
    {
        $this->_stackElements     = $items;
        $this->_stackElementsKeys = new ArrayCollection($items->getKeys());
        return $this;
    }

    /**
     * Gets row from stack.
     * It can be used in findOneBy{$paramName}() convention;
     *
     * @param integer $id
     * @param string  $mapping[optional]     - ORM mapping column
     * @return mixed
     * @throws NotFoundHttpException
     * @throws NoResultException
     */
    public function pickRow($id, $mapping = null)
    {
        $this->_initialize();

        if (!$this->_issetId($id)) {
            throw new NotFoundHttpException('Id(' . $id . ') is not found in stack.');
        }
        if (empty($this->_oneLoadMethod)) {
            $this->_oneLoadMethod = (null !== $mapping)
                ? 'findOneBy' . ucfirst($mapping)
                : 'find';
        }

        $result = $this->getFilter()
            ->getCollection()
            ->get($id, $this->_oneLoadMethod);

        if (!$this->_isGetRowResultValid($result)) {
            throw new NoResultException('Item not found.');
        }
        $this->_pickedElement = new ListingRow($this->createView(), $result);
        return $this->_pickedElement;
    }

    /**
     * Adds static parameter for filtering data
     *
     * @param string    $name
     * @param mixed     $value
     * @param string    $mapping - ORM Mapping
     * @param string    $condition[optional] - default: '='
     * @return $this
     */
    public function addFilterParameter($name, $value, $mapping, $condition = '=')
    {
        $this->getFilter()->createParameter($name, $value, $condition, array(
            'mapping'  => $mapping,
            'hidden'   => true,
            'readonly' => true,
        ));
        return $this;
    }

    /**
     * Checks if Id exists in stack
     *
     * @param integer $id
     * @return boolean
     */
    protected function _issetId($id)
    {
        $stack = $this->getStackRowsIdentifiers();
        return !($stack->count() == 0 || !in_array($id, $stack->toArray()));
    }

    /**
     * Checks if getRow() method result contains any useful data.
     *
     * @param mixed $result
     * @return bool
     */
    protected function _isGetRowResultValid($result)
    {
        return is_object($result) || (is_array($result) && !empty($result));
    }

}