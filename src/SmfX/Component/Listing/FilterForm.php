<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


use SmfX\Component\Listing\Form\DataTransformer;
use Symfony\Component\Form\Form;

class FilterForm
{
    /**
     * @var Form
     */
    protected $_form;

    /**
     * @var DataTransformer
     */
    protected $_dataTransformer;

    /**
     * Construct
     *
     * @param mixed             $form
     * @param DataTransformer   $dataTransformer [optional]
     */
    public function __construct($form, DataTransformer $dataTransformer = null)
    {
        $this->_form            = $form;
        $this->_dataTransformer = $dataTransformer;
        var_dump($this->_dataTransformer); exit;
    }

    /**
     * Handles Input data
     *
     * @param Input $input
     * @return $this
     */
    public function handleInput(Input $input)
    {
        $input->handle($this->_form);
        return $this;
    }

    /**
     * Is Valid
     *
     * @return bool
     */
    public function isValid()
    {
        if ($this->_form->isSubmitted()) {
            return $this->_form->isValid();
        }
        return true;
    }

    /**
     * Gets form data
     *
     * @return mixed
     */
    public function getData()
    {
        return array($this->_form->getName() => $this->_form->getData());
    }

    /**
     * Sets form data
     *
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $formData = isset($data[$this->_form->getName()]) ? $data[$this->_form->getName()] : null;
        $this->_form->setData($formData);
        return $this;
    }

    /**
     * Gets parameters
     *
     * @return mixed
     */
    public function getParameters()
    {
        $parameters = array();

        //todo: FilterFormInterface
        foreach ($this->_form->getIterator() as $item) {
            /** @var Form $item */
            $name       = $item->getName();
            $mapping    = $item->getConfig()->getOption('listing_filter_mapping');
            $condition  = $item->getConfig()->getOption('listing_filter_condition');
            $expression = $item->getConfig()->getOption('listing_filter_expression');

            if (null !== $mapping || null !== $expression) {
                $value      = $item->getData();
                $emptyValue = '';
                switch ($item->getConfig()->getType()->getName()) {
                    //todo: file
                    case 'choice':
                        $output = T_ARRAY;
                        $emptyValue = array('');
                        //todo: checkbox - unchecked value
                        break;

                    case 'datetime':
                    case 'date':
                        $output = 'datetime';
                        break;

                    default:
                        $output = T_STRING;
                }

                //todo: restriction
                $spec = array(
                    'mock'        => false,
                    'filters'     => null,
                    'mapping'     => $mapping,
                    'outputType'  => $output,
                    'readonly'    => null,
                    'hidden'      => null,
                    'expression'  => $expression,
                    'emptyValue'  => $emptyValue,
                    'restriction' => null,
                );
                $parameters[] = new FilterParameter($name, $value, $condition, $spec);
            }
        }
        return $parameters;
    }

    /**
     * Creates view
     *
     * @return mixed
     */
    public function createView()
    {
        return $this->_form->createView();
    }
} 