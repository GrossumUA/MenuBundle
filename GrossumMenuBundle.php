<?php

namespace Grossum\MenuBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Grossum\MenuBundle\DependencyInjection\CompilerPass\MenuHandlerPass;

class GrossumMenuBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MenuHandlerPass());
    }
}
