<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


interface StorageAdapterInterface
{
    /**
     * Method sets node key
     *
     * @param string $key
     * @return $this
     */
    public function setKey($key);
} 