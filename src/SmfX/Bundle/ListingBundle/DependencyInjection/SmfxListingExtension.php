<?php
/** 
 * @package   SmfX
 * @author    Michał Pierzchalski <michal.pierzchalski@gmail.com>
 * @license   MIT
 */

namespace SmfX\Bundle\ListingBundle\DependencyInjection;

use SmfX\Component\Listing\ListingContainer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Finder;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class SmfxListingExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerExtension($this);

        $configuration = $this->getConfiguration($configs, $container);
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $finder = new Finder\Finder();
        $finder
            ->directories()
            ->in($container->getParameter('kernel.root_dir') . '/../src/*/*/*Bundle/Resources')
            ->in(realpath(__DIR__.'/../Resources'))
            ->path('config');

        $possibleLocations = array();
        foreach ($finder as $dir) {
            /**
             * @var Finder\SplFileInfo $dir
             */
            $possibleLocations[] = $dir->getPathName();
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator($possibleLocations));
        $loader->load('listings.yml');
    }

}