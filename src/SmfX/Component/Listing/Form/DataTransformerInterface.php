<?php
/**
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing\Form;


interface DataTransformerInterface
{
    /**
     * Checks if data could be transformed by this transformer
     *
     * @param mixed $data
     * @return mixed
     */
    public function valid($data);

    /**
     * Transforms provided data
     *
     * @param mixed $data
     * @return mixed
     */
    public function transform($data);

    /**
     * Gets unique data transfer's adapter name
     *
     * @return string
     */
    public function getName();
} 