<?php
/**
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;

class ListingSnapshot
{
    /**
     * @var Listing
     */
    private $_listing;

    /**
     * @var array
     */
    public $snapshot = array();

    /**
     * @param Listing $listing
     */
    public function __construct(Listing $listing)
    {
        $this->_listing = $listing;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        $this->snapshot = array(
            'name'          => $this->_listing->getName(),
            'identifiers'   => $this->_listing->getStackRows()->getKeys(),
            'currentPage'   => $this->_listing->createView()->getCurrentPage(),
            'pageLimit'     => $this->_listing->createView()->getPageLimit(),
            'filter'        => $this->_listing->getFilter()->createSnapshot(),
        );
        $this->_listing = null;
        return array('snapshot');
    }

    /**
     * Gets listing name
     *
     * @return string
     */
    public function getName()
    {
        return $this->snapshot['name'];
    }

    /**
     * Gets stack rows identifiers
     *
     * @return array
     */
    public function getIdentifiers()
    {
        return $this->snapshot['identifiers'];
    }

    /**
     * Gets current page number
     *
     * @return mixed
     */
    public function getCurrentPage()
    {
        return $this->snapshot['currentPage'];
    }

    /**
     * Gets page limit
     *
     * @return mixed
     */
    public function getPageLimit()
    {
        return $this->snapshot['pageLimit'];
    }

    /**
     * Gets filter data
     *
     * @return array
     */
    public function getFilter()
    {
        return (isset($this->snapshot['filter'])) ? $this->snapshot['filter'] : array();
    }
}