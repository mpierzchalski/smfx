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
        );
        if (($filter = $this->_listing->getFilter()) instanceof Filter) {
            if (($form = $filter->getForm()) !== null) {
                $this->snapshot['formData'] = $form->getData();
            }
        }
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
     * Gets form data
     *
     * @return array
     */
    public function getFormData()
    {
        return (isset($this->snapshot['formData'])) ? $this->snapshot['formData'] : array();
    }
}