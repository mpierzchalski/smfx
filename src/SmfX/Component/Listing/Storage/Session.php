<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing\Storage;


use SmfX\Component\Listing\StorageAdapterInterface;
use SmfX\Component\Listing\StorageInterface;
use Symfony\Component\HttpFoundation\Session\Session as SessionService;

class Session implements StorageInterface, StorageAdapterInterface
{

    /**
     * @var SessionService
     */
    protected $_session;

    /**
     * @var string
     */
    protected $_key = '';

    /**
     * @param SessionService $session
     */
    function __construct(SessionService $session)
    {
        $this->_session = $session;
    }

    /**
     * Method sets node key
     *
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->_key = $key;
        return $this;
    }

    /**
     * Does storage is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return !isset($this->_session->{$this->_key});
    }

    /**
     * Method reads storage
     *
     * @return mixed
     */
    public function read()
    {
        return $this->_session->{$this->_key};
    }

    /**
     * Method saves data in storage
     *
     * @param mixed $data
     * @return void
     */
    public function write($data)
    {
        $this->_session->{$this->_key} = $data;
    }

    /**
     * Method removes data
     *
     * @return void
     */
    public function clear()
    {
        unset($this->_session->{$this->_key});
    }

} 