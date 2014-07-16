<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


interface StorageInterface
{
    /**
     * Does storage is empty
     *
     * @return boolean
     */
    public function isEmpty();

    /**
     * Method reads storage
     *
     * @return ListingSnapshot|null
     */
    public function read();

    /**
     * Method saves data in storage
     *
     * @param mixed $data
     * @return void
     */
    public function write($data);

    /**
     * Method removes data
     *
     * @return void
     */
    public function clear();
}