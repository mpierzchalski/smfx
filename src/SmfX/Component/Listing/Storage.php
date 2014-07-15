<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;

class Storage implements StorageInterface
{
    /**
     * @var string
     */
    protected $_namespace = '';

    /**
     * @var string
     */
    protected $_key = '';

    /**
     * @var StorageInterface
     */
    protected $_adapter;

    /**
     * @param StorageAdapterInterface $adapter
     * @param string $key
     * @param string $namespace
     */
    function __construct(StorageAdapterInterface $adapter, $namespace, $key)
    {
        $this->_adapter     = $adapter;
        $this->_key         = $key;
        $this->_namespace   = $namespace;

        $this->_adapter->setKey($key);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->_namespace;
    }

    /**
     * Does storage is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->_adapter->isEmpty();
    }

    /**
     * Method reads storage
     *
     * @return mixed
     */
    public function read()
    {
        return $this->_adapter->read();
    }

    /**
     * Method saves data in storage
     *
     * @param mixed $data
     * @return void
     */
    public function write($data)
    {
        $this->_adapter->write($data);
    }

    /**
     * Method removes data
     *
     * @return void
     */
    public function clear()
    {
        $this->_adapter->clear();
    }

} 