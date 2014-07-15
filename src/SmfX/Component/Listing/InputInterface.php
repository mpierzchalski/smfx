<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


interface InputInterface
{
    /**
     * Construct
     *
     * @param mixed $component
     */
    public function __construct($component);

    /**
     * Handles input object to filter. It provides output data for filter.
     */
    public function handle($filter);

    /**
     * Gets output format
     *
     * @return string
     */
    public function getOutputFormat();

    /**
     * Sets output format
     *
     * @param string $outputFormat
     * @return $this
     */
    public function setOutputFormat($outputFormat);

    /**
     * Gets filter parameters
     *
     * @return array
     */
    public function getFilterParameters();

    /**
     * Sets filter parameters
     *
     * @param array $parameters
     * @return $this
     */
    public function setFilterParameters($parameters);
} 