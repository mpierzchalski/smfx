<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


use SmfX\Component\Collection\FilteredCollection;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class ListingView
{
    /**
     * @var Listing
     */
    protected $_list;

    /**
     * @var Router
     */
    protected $_router;

    /**
     * Construct
     *
     * @param Listing   $list
     * @param Router    $router
     */
    public function __construct(Listing $list, Router $router)
    {
        $this->_list   = $list;
        $this->_router = $router;
    }

    /**
     * Gets filter form view statement
     */
    public function getFilterForm()
    {
        return (($form = $this->_list->getFilter()->getForm()) instanceof FilterForm)
            ? $form->createView()
            : null;
    }

    /**
     * Gets rows
     *
     * @return array
     */
    public function getRows()
    {
        if (($stackRows = $this->_list->getStackRows()) instanceof FilteredCollection) {
            return $stackRows->toArray();
        }
        return array();
    }

    /**
     * Gets Urls
     *
     * @param boolean   $view [optional]            - TRUE: for HTML version
     * @param array     $extendedParams [optional]  - set overwritten params
     *
     * @return string
     */
    public function getUrl($view = null, $extendedParams = array())
    {
        $params = array();
        if (($filter = $this->_list->getFilter()) !== null) {
            $params = $filter->getCollection()->getFilter()->getPublicQueryParameters();
        }
        if (!empty($extendedParams)) {
            $params = array_merge($params, $extendedParams);
        }
        $url = $this->_router->generate($this->_list->getConfig()['route'], $params);
        if (true === $view) {
            $url = preg_replace('/\&/', '&amp;', $url);
        }
        return $url;
    }

    /**
     * Gets number of first row in [page
     *
     * @return int
     */
    public function getFirstRowNo()
    {
        return $this->getPageLimit()*($this->getCurrentPage()-1);
    }

    /**
     * Gets limiter choices
     *
     * @return array
     */
    public function getLimiterChoices()
    {
        return Filter::limiterOptions();
    }

    /**
     * Gets page limit
     *
     * @return int
     */
    public function getPageLimit()
    {
        return $this->_list->getFilter()->getPageLimit();
    }

    /**
     * Checks if list is paginated
     *
     * @return bool
     */
    public function isPaginated()
    {
        return $this->_list->getFilter()->isPaginated();
    }

    /**
     * Gets total amount of rows
     *
     * @return int
     */
    public function getRowsAmount()
    {
        return $this->_list->getFilter()->getCollection()->getTotalQuantity();
    }

    /**
     * Gets current page number
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->_list->getFilter()->getCollection()->getCurrentPart();
    }

    /**
     * Gets last page number
     *
     * @return int
     */
    public function getLastPage()
    {
        return $this->_list->getFilter()->getCollection()->getPagesAmount();
    }
}