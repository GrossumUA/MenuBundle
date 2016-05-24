<?php

namespace Grossum\MenuBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Grossum\MenuBundle\Entity\BaseMenu;
use Grossum\MenuBundle\Entity\EntityManager\BaseMenuItemManager;

class MenuListener
{
    /**
     * @var ContainerInterface
     */
    protected $serviceContainer;

    /**
     * @var BaseMenuItemManager
     */
    protected $menuItemManager;

    /**
     * We inject service container here because this service processed by "doctrine.orm.default_entity_manager" and this
     * service depends on "grossum_menu.entity.manager.menu_item.manager" which depends on
     * "doctrine.orm.default_entity_manager" so we have circular reference.
     *
     * @param ContainerInterface $serviceContainer
     */
    public function __construct(ContainerInterface $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return BaseMenuItemManager
     */
    protected function getMenuItemManager()
    {
        if (null === $this->menuItemManager) {
            $this->menuItemManager = $this->serviceContainer->get('grossum_menu.entity.manager.menu_item.manager');
        }

        return $this->menuItemManager;
    }

    /**
     * @param BaseMenu $menu
     * @param LifecycleEventArgs $event
     */
    public function prePersist(BaseMenu $menu, LifecycleEventArgs $event)
    {
        $this->getMenuItemManager()->createRootMenuItemForMenu($menu);
    }
}
