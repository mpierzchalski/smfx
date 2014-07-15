<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;
use SmfX\Component\Collection\FilteredCollection;
use SmfX\Component\Listing\Exceptions\Listing\NoFilterException;
use Symfony\Component\HttpFoundation\Request;

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
    private $_mode = self::MODE_FULL;

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
     * @var ListingView
     */
    private $_view;

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
     * Calls service's methods and decorate data in collector object
     *
     * @param string $name
     * @param array $arguments
     * @return ListingResult
     * @throws NoFilterException
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
            return new ListingResult(
                ListingResult::FAILURE_FILTER,
                ListingResult::TYPE_ERROR,
                "Seems filter contains some errors!"
            );
        } else {
            $items = $filter
                ->setArguments($arguments)
                ->loadStack($name);

            if (!$items instanceof FilteredCollection) {
                return new ListingResult(
                    ListingResult::FAILURE_FILTER,
                    ListingResult::TYPE_ERROR,
                    "Some errors have been occurred during reading data!"
                );
            }
            $this->setStackRows($items);
        }
        return $this;
    }

    /**
     * Creates View object
     *
     * @return ListingView
     */
    public function createView()
    {
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
     * Sets list result
     *
     * @param FilteredCollection $items [optional]
     * @return $this
     */
    public function setStackRows(FilteredCollection $items = null)
    {
        $this->_stackElements = $items;
        return $this;
    }

}