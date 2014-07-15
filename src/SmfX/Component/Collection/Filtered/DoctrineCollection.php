<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Collection\Filtered;

use Doctrine\Bundle\DoctrineBundle\Registry;
use SmfX\Component\Collection\FilteredCollection;
use SmfX\Component\Listing\Filter\AdapterInterface as FilterAdapterInterface;

class DoctrineCollection extends FilteredCollection
{
    /**
     * Default limit
     */
    const LIMIT_LOADER = 10;

    /**
     * @var Registry
     */
    protected $_doctrine;

    /**
     * @var string
     */
    protected $_repository;

    /**
     * @var string
     */
    protected $_method;

    /**
     * @var string
     */
    protected $_identifier = 'id';

    /**
     * @var boolean
     */
    protected $_useDecorator = false;

    /**
     * Constructor overwrites parent construct in order to provide doctrine2 container.
     *
     * @param Registry               $doctrine
     * @param FilterAdapterInterface $filter
     * @param array                  $elements
     */
    public function __construct(Registry $doctrine, FilterAdapterInterface $filter, array $elements = array())
    {
        $this->_doctrine = $doctrine;
        parent::__construct($filter, $elements);
    }


    /**
     * @return array
     */
    public function __sleep()
    {
        $this->_doctrine = null;
        return array_keys(get_object_vars($this));
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig($config)
    {
        parent::setConfig($config);
        $this->_repository   = $this->_config['entityName'];
        $this->_method       = $this->_config['loadMethod'];

        if (isset($this->_config['identifier'])) {
            $this->_identifier = $this->_config['identifier'];
        }
        if (isset($this->_config['entityDecorator'])) {
            $this->_useDecorator = (boolean)$this->_config['entityDecorator'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getKeys()
    {
        if (!$this->_elements) {
            return array();
        }

        $identifiers = array();
        foreach ($this->_elements as &$element) {
            if (!$identifier = $this->getIdentifier($element)) {
                continue;
            }
            $identifiers[] = (is_array($identifier) ? current($identifier) : $identifier);
        }
        unset($element);
        return $identifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if (!empty($this->_elements)) {
            $adapter = $this->_getEm();
            $current = current($this->_elements);
            if (is_object($current)) {
                array_walk($this->_elements, function($val, $key) use($adapter) {
                    $adapter->detach($val);
                });
            }
            $this->_elements = array();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $method     = func_get_arg(1);
        $repository = $this->_getEm()->getRepository($this->_repository);
        if (is_object($repository)) {
            $result = call_user_func_array(array($repository, $method), array($key));
            if ($this->_useDecorator) {
                //todo: decorators
            }
            return $result;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function slice($offset, $length = null)
    {
        $repository = $this->_getEm()->getRepository($this->_repository);
        if (is_object($repository)) {
            $result = call_user_func_array(array($repository, $this->_method), $this->_filterParams);
            if ($this->_useDecorator) {
                array_walk($result, function(&$item, $key) {
                    //todo: decorators
                });
            }
            if (!empty($result)) {
                return $result;
            }
        }
        return array();
    }

    /**
     * Gets EntityManager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function _getEm()
    {
        $entityManager = (isset($this->_config['manager'])) ? $this->_config['manager'] : null;
        return $this->_doctrine->getManager($entityManager);
    }

    /**
     * Gets identifier
     *
     * @param object $entity
     * @return mixed
     */
    public function getIdentifier($entity)
    {
        $method = null;
        if ($this->_identifier) {
            $method = 'get' . ucfirst($this->_identifier);
        }
        return $this->_getIdentifier($entity, $method);
    }

    /**
     * Gets identifier from metadata
     *
     * @param object $entity
     * @param string $method[optional]
     * @return string
     * @throws \OutOfBoundsException
     */
    protected function _getIdentifier($entity, $method = null)
    {
        $identifier = null;
        if (is_array($entity)) {
            foreach ($entity as $row) {
                if (is_object($row)) {
                    $entity = $row;
                }
                break;
            }
        }

        //todo: decorators..
        try {
            if (null === $method) {
                $meta     = $this->_getEm()->getClassMetadata(get_class($entity));
                $meta     = $meta->getIdentifier();
                $method   = 'get' . ucfirst(reset($meta));
            }
            $identifier = $entity->$method();
        } catch (\Exception $e) {}

        if (!$identifier) {
            throw new \OutOfBoundsException('There is no identifier in ' . get_class($entity) . ' entity!');
        }
        return $identifier;
    }
} 