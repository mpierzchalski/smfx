<?php
/**
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing\Form\DataTransformer;


use SmfX\Component\Listing\Form\DataTransformerInterface;

class Standard implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function valid($data)
    {
        return !is_object($data) || !is_resource($data);
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'standard';
    }
} 