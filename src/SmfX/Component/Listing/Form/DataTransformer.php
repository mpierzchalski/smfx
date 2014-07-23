<?php
/**
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing\Form;


class DataTransformer
{
    /**
     * @var DataTransformerInterface[]
     */
    private $_transformers = array();

    /**
     * Adds data transformer
     *
     * @param DataTransformerInterface $transformer
     * @return $this
     */
    public function addTransformer(DataTransformerInterface $transformer)
    {
        $this->_transformers[$transformer->getName()] = $transformer;
        return $this;
    }

    /**
     * Transforms data
     *
     * @param mixed $data
     * @return array|mixed
     * @throws \OutOfBoundsException
     */
    public function transform($data)
    {
        if (is_array($data)) {
            $return = [];
            foreach ($data as $k => $value) {
                $return[$k] = $this->transform($value);
                unset($value);
            }
            return $return;
        } else {
            foreach ($this->_transformers as &$transformer) {
                if ($transformer->valid($data)) {
                    return $transformer->transform($data);
                }
            }
            throw new \OutOfBoundsException(
                'DataTransformer exception! Non of defined transformers was not able to transform provided data.'
            );
        }
    }
} 