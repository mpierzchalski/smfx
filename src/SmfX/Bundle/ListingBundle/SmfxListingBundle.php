<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Bundle\ListingBundle;

use SmfX\Bundle\ListingBundle\DependencyInjection\Compiler\SmfxListingCompilerPass;
use SmfX\Bundle\ListingBundle\DependencyInjection\Compiler\SmfxListingContainerCompilerPass;
use SmfX\Bundle\ListingBundle\DependencyInjection\SmfxListingExtension;
use SmfX\Bundle\ListingBundle\DependencyInjection\SmfxListingContainerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SmfxListingBundle
 * @package SmfX\Bundle\ListingBundle
 */
class SmfxListingBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->registerExtension(new SmfxListingContainerExtension());
        $container->addCompilerPass(new SmfxListingContainerCompilerPass());
        $container->addCompilerPass(new SmfxListingCompilerPass());
    }

}
