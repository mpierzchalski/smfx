<?php
/**
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


class ListingRow
{
    /**
     * @var mixed
     */
    private $_data;

    /**
     * @var ListingView
     */
    private $_listing;

    /**
     * Construct
     *
     * @param ListingView   $listing
     * @param mixed         $data
     */
    public function __construct(ListingView $listing, $data)
    {
        $this->_listing = $listing;
        $this->_data     = $data;
    }

    /**
     * Gets row data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Gets listing URL
     *
     * @param boolean $view
     * @return string
     */
    public function getListingUrl($view)
    {
        return $this->_listing->getUrl($view);
    }
}