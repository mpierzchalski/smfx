<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


class ListingResult
{
    /**
     * Result codes
     */
    const FAILURE_FILTER    = -20;
    const FAILURE_LISTING   = -10;
    const NO_ACTION         = 0;
    const SUCCESS           = 1;

    /**
     * Types
     */
    const TYPE_ERROR        = 'alert';
    const TYPE_WARNING      = 'warning';
    const TYPE_INFO         = 'info';
    const TYPE_SUCCESS      = 'success';

    /**
     * @var int
     */
    private $_code;

    /**
     * @var string
     */
    private $_type;

    /**
     * @var null|string
     */
    private $_message;

    /**
     * @var null|string
     */
    private $_redirect;

    /**
     * Konstruktor
     *
     * @param integer $code
     * @param string $type
     * @param string $message
     * @param string $redirect
     * @param bool $flashMessage
     */
    public function __construct($code, $type, $message = null, $redirect = null, $flashMessage = false)
    {
        $code = (int) $code;
        if ($code > self::SUCCESS) {
            $code = 1;
        }

        $this->_code     = $code;
        $this->_type     = $type;
        $this->_message  = $message;
        $this->_redirect = $redirect;

        if (true === $flashMessage) {
            $this->addNowFlashMessage($message, $type);
        }
    }

    /**
     * Gets result code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Gets type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Gets message
     *
     * @return null|string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Gets redirection routes name
     *
     * @return null|string
     */
    public function getRedirect()
    {
        return $this->_redirect;
    }

    /**
     * Adds flash message
     *
     * @param $message
     * @param $type
     * @throws \Exception
     */
    public function addNowFlashMessage($message, $type)
    {
        throw new \Exception("Not implemented yet!");
    }
} 