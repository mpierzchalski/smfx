<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Bundle\ListingBundle\Input;


use SmfX\Component\Listing\InputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class RequestInput implements InputInterface
{
    /**
     * @var Request
     */
    private $_request;

    /**
     * Construct
     *
     * @param Container $component
     */
    public function __construct($component)
    {
        $this->_request = $component->get('request');
    }

    /**
     * {@inheritdoc}
     */
    public function handle($filter)
    {
        /** @var Form $filter */
        $filter->handleRequest($this->_request);
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputFormat()
    {
        return $this->_request->get('output_format');
    }

    /**
     * {@inheritdoc}
     */
    public function setOutputFormat($outputFormat)
    {
        $this->_request->request->set('output_format', $outputFormat);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterParameters()
    {
        //todo: form isn't static ... should be set
        return $this->_request->request->get('form');
    }

    /**
     * {@inheritdoc}
     */
    public function setFilterParameters($parameters)
    {
        //todo: form isn't static ... should be set
        $this->_request->request->set('form', $parameters);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->_request->get($name);
    }
}