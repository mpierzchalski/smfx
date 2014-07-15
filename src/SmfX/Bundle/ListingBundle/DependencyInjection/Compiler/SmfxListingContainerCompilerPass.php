<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */
 
namespace SmfX\Bundle\ListingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class SmfxListingContainerCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $listingContainer = $container->getDefinition('smfx.listing.container');
        $listingContainer->addMethodCall('setServiceContainer', array(
            new Reference('service_container')
        ));

        $this->loadStorage($listingContainer, $container);
    }

    /**
     * Define storage instance
     *
     * @param Definition $listingContainer
     * @param ContainerBuilder $container
     * @return Definition
     */
    public function loadStorage(Definition $listingContainer, ContainerBuilder $container)
    {
        $config = $container->get('smfx.listing.container')->getConfig();
        $listingContainer->addMethodCall('setStorageAdapter', array(
            new Reference($config['storage']),
            $config['namespace']
        ));
    }
} 