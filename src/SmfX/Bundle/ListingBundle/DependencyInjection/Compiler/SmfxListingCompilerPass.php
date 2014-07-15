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

class SmfxListingCompilerPass implements CompilerPassInterface
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
        $config = $container->getExtensionConfig('smfx_listing');
        if (empty($config)) {
            return true;
        }

        $listingContainer = $container->getDefinition('smfx.listing.container');
        foreach ($config as $spec) {
            foreach ($spec as $name => $config) {
                $listing = new Definition('SmfX\\Component\\Listing\\Listing', array($name, $config));
                $listing->addMethodCall('register', array($listingContainer));
                $container->setDefinition('smfx.listings.' . $name, $listing);
            }
        }
    }

} 