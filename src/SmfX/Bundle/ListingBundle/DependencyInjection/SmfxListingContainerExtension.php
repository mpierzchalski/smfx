<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Bundle\ListingBundle\DependencyInjection;

use SmfX\Bundle\ListingBundle\DependencyInjection\Compiler\SmfxListingContainerCompilerPass;
use SmfX\Component\Listing\ListingContainer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Finder;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SmfxListingContainerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        //todo: normalizing configuration
        $config = $configs[0];
//        $configuration = $this->getConfiguration($configs, $container);
//        var_dump($this->processConfiguration($configuration, $configs)); exit;

        $listingContainer = new Definition('SmfX\\Component\\Listing\\ListingContainer', array($config));
        $container->setDefinition('smfx.listing.container', $listingContainer);
    }

}