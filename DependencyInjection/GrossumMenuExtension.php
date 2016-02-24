<?php

namespace Grossum\MenuBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class GrossumMenuExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('classes.yml');
        $loader->load('admin.yml');

        $bundles = $container->getParameter('kernel.bundles');

        if (!isset($bundles['StofDoctrineExtensionsBundle'])) {
            throw new \RuntimeException('Menu bundle requires a Stof Doctrine Extensions Bundle');
        }

        $this->configureParameterClass($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    public function configureParameterClass(ContainerBuilder $container, array $config)
    {
        $container->setParameter('grossum_menu.menu.entity.class', $config['class']['menu']);
    }
}
