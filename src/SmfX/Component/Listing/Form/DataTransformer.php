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
     * Transforms data to snapshot format
     *
     * @param mixed $data
     * @return array|mixed
     * @throws \OutOfBoundsException
     */
    public function transformForSnapshot($data)
    {
        if (is_array($data)) {
            $return = [];
            foreach ($data as $k => $value) {
                $return[$k] = $this->transformForSnapshot($value);
                unset($value);
            }
            return $return;
        } else {
            foreach ($this->_transformers as &$transformer) {
                if ($transformer->valid($data)) {
                    return array(
                        'transformer' => $transformer->getName(),
                        'data'        => $transformer->transformForSnapshot($data)
                    );
                }
            }
            throw new \OutOfBoundsException(
                'DataTransformer exception! Non of defined transformers was not able to transform provided data.'
            );
        }
    }

    /**
     * Transforms data from snapshot format
     *
     * @param mixed $data
     * @return array|mixed
     * @throws \OutOfBoundsException
     */
    public function transformFromSnapshot($data)
    {
        if (array_key_exists('transformer', $data) && array_key_exists('data', $data)) {
            if (!array_key_exists($data['transformer'], $this->_transformers)) {
                throw new \OutOfBoundsException(
                    'DataTransformer named ' . $data['transformer'] . ' does not exist in transformers stack.'
                );
            }
            return $this->_transformers[$data['transformer']]->transformFromSnapshot($data['data']);

        } else if (is_array($data)) {
            $return = [];
            foreach ($data as $k => $value) {
                $return[$k] = $this->transformFromSnapshot($value);
                unset($row);
            }
            return $return;
        } else {
            return $data;
        }
    }
} 