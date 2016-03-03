<?php

namespace Grossum\MenuBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MenuHandlerPass implements CompilerPassInterface
{
    const TAG = 'grossum_menu.menu_handler';
    const REGISTRY_SERVICE = 'grossum_menu.menu.manager';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::REGISTRY_SERVICE)) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds(self::TAG);

        if (empty($taggedServices)) {
            return;
        }

        $registryDefinition = $container->getDefinition(self::REGISTRY_SERVICE);

        foreach (array_keys($taggedServices) as $id) {
            $registryDefinition->addMethodCall('addMenuHandler', [new Reference($id)]);
        }
    }
}