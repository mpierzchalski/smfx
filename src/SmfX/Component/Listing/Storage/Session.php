<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing\Storage;


use SmfX\Component\Listing\ListingSnapshot;
use SmfX\Component\Listing\StorageAdapterInterface;
use SmfX\Component\Listing\StorageInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Session as SessionService;

class Session implements StorageInterface, StorageAdapterInterface
{

    /**
     * Name of attribute bag
     */
    const BAG_NAME = 'smfx_listing';

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
        return false === $this->_session->getBag(self::BAG_NAME)->get($this->_key, false);
    }

    /**
     * Method reads storage
     *
     * @return ListingSnapshot|null
     */
    public function read()
    {
        return $this->_session->getBag(self::BAG_NAME)->get($this->_key);
    }

    /**
     * Method saves data in storage
     *
     * @param mixed $data
     * @return void
     */
    public function write($data)
    {
        $this->_session->getBag(self::BAG_NAME)->set($this->_key, $data);
    }

    /**
     * Method removes data
     *
     * @return void
     */
    public function clear()
    {
        $this->_session->getBag(self::BAG_NAME)->clean();
    }

} 