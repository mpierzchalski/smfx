<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */
 
namespace SmfX\Bundle\ListingBundle\DependencyInjection\Compiler;

use SmfX\Component\Listing\Storage\Session;
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

        $listingContainer   = $container->getDefinition('smfx.listing.container');
        $dataTransformer    = $container->getDefinition('smfx_listing.form.data_transformer');
        $taggedTransformers = $container->findTaggedServiceIds('smfx_listing.form.data_transformer');
        foreach ($taggedTransformers as $id => $attributes) {
            $dataTransformer->addMethodCall('addTransformer', array(new Reference($id)));
        }

        foreach ($config as $spec) {
            foreach ($spec as $name => $config) {
                $listing = new Definition('SmfX\\Component\\Listing\\Listing', array($name, $config));
                $listing->addMethodCall('register', array($listingContainer));
                $container->setDefinition('smfx.listings.' . $name, $listing);
            }
        }
        $registerBag = new Definition(
            $container->getParameter('session.attribute_bag.class'),
            array(Session::BAG_NAME . '_attributes')
        );
        $registerBag->addMethodCall('setName', array(Session::BAG_NAME));
        $container->getDefinition('session')->addMethodCall('registerBag', array($registerBag));
    }

} 