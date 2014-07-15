<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ListingContainer
{
    /**
     * @var array
     */
    static protected $_stack = array();

    /**
     * @var array
     */
    protected $_config = array();

    /**
     * @var Container
     */
    protected $_serviceContainer;

    /**
     * @var StorageInterface
     */
    protected $_storageAdapter;

    /**
     * @var string
     */
    protected $_storageNamespace;

    /**
     * Constructor sets configuration
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    /**
     * Method returns configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param Container $serviceContainer
     * @return $this
     */
    public function setServiceContainer(Container $serviceContainer)
    {
        $this->_serviceContainer = $serviceContainer;
        return $this;
    }

    /**
     * Set StorageInterface
     *
     * @param StorageInterface $adapter
     * @param $namespace
     * @return $this
     */
    public function setStorageAdapter(StorageInterface $adapter, $namespace)
    {
        $this->_storageAdapter   = $adapter;
        $this->_storageNamespace = $namespace;
        return $this;
    }

    /**
     * Returns Routers instance
     *
     * @throws \InvalidArgumentException
     * @return Router
     */
    public function getRouter()
    {
        if (!$this->_serviceContainer instanceof Container) {
            throw new \InvalidArgumentException('Service container must be provided before!');
        }
        return $this->_serviceContainer->get('router');
    }

    /**
     * Add listing in stack
     *
     * @param Listing $listing
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function add(Listing $listing)
    {
        if (!array_key_exists($listing->getName(), self::$_stack)) {
            self::$_stack[$listing->getName()] = $listing;

            $listing->setStorage($this->getStorage($listing->getName()));
            $input = new Input($this->_serviceContainer->get('smfx_listing.input_adapter'));

            if (isset($listing->getConfig()['service']) && empty($listing->getConfig()['service'])) {
                $listing->setService($this->_serviceContainer->get($listing->getConfig()['service']));
            }

            if (!isset($listing->getConfig()['filter']) || empty($listing->getConfig()['filter'])) {
                throw new \InvalidArgumentException('Unknown listing filter class!');
            }
            $filterConfig  = $listing->getConfig()['filter'];
            $filterBuilder = new FilterBuilder($filterConfig);
            $filter        = $filterBuilder->build($this->_serviceContainer);
            $listing
                ->setFilter($filter)
                ->setInput($input);

            $listing->registerView(new ListingView($listing, $this->getRouter()));
        }
        return $this;
    }

    /**
     * @param $name
     * @return StorageInterface
     */
    public function getStorage($name)
    {
        return new Storage($this->_storageAdapter, $this->_storageNamespace, $name);
    }

}