<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


class Input implements InputInterface
{
    /**
     * @var InputInterface
     */
    private $_component;

    /**
     * {@inheritdoc}
     */
    public function __construct($component)
    {
        $this->_component = $component;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($filter)
    {
        $this->_component->handle($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputFormat()
    {
        return $this->_component->getOutputFormat();
    }

    /**
     * {@inheritdoc}
     */
    public function setOutputFormat($outputFormat)
    {
        $this->_component->setOutputFormat($outputFormat);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterParameters()
    {
        return $this->_component->getFilterParameters();
    }

    /**
     * {@inheritdoc}
     */
    public function setFilterParameters($parameters)
    {
        $this->_component->setFilterParameters($parameters);
        return $this;
    }
}