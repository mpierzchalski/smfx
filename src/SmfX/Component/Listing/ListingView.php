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
        return $this->_list->getFilter()->getForm()->createView();
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
            $url = urlencode($url);
        }
        return $url;
    }
} 