<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Component\Listing;


use SmfX\Component\Listing\Exceptions\Listing\FilterBuilderException;
use Symfony\Component\DependencyInjection\Container;

class FilterBuilder
{

    /**
     * Prefix for listings filter service
     */
    const SERVICE_PREFIX = 'smfx_listing.filter_';

    /**
     * @var string
     */
    protected $_name = '';

    /**
     * @var FilterConfigBuilder
     */
    protected $_config;

    /**
     * Construct
     *
     * @param array $config
     * @throws FilterBuilderException
     */
    public function __construct($config = null)
    {
        if (!isset($config['adapter']) && empty($config['adapter'])) {
            throw new FilterBuilderException("There is no adapter node defined in listing configuration!");
        }
        $this->_name   = self::SERVICE_PREFIX . strtolower(trim($config['adapter']));
        $this->_config = new FilterConfigBuilder($config);
    }

    /**
     * Builds filter service instance
     *
     * @param Container $container
     * @return Filter
     */
    public function build(Container $container)
    {
        /** @var \SmfX\Component\Listing\Filter\AdapterInterface $adapter */
        $adapter    = $container->get($this->_name);
        $collection = $container->get($adapter->getCollectionName());
        $form       = $this->_buildForm($container);
        return new Filter($collection, $this->_config, $form);
    }

    /**
     * Builds form filter object
     *
     * @param Container $container
     * @return null|object
     */
    private function _buildForm(Container $container)
    {
        if (($formIdService = $this->_config->getForm()) !== null) {
            $form = $container->get($formIdService);

        } else if (($formClassName = $this->_config->getFormClass()) !== null) {
            $form = new $formClassName;
        }
        if (isset($form)) {
            return new FilterForm($container->get('form.factory')->create($form));
        }
        return null;
    }
} 