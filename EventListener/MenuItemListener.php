<?php

namespace Grossum\MenuBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use Grossum\MenuBundle\Entity\BaseMenuItem;
use Grossum\MenuBundle\Entity\EntityManager\BaseMenuItemManager;

class MenuItemListener
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
     * @var BaseMenuItem[]
     */
    protected $persistedMenuItems = [];

    /**
     * @var BaseMenuItem[]
     */
    protected $updatedMenuItems = [];

    /**
     * @var BaseMenuItem[]
     */
    protected $removedMenuItems = [];

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
     * @param BaseMenuItem $menuItem
     * @param LifecycleEventArgs $event
     */
    public function prePersist(BaseMenuItem $menuItem, LifecycleEventArgs $event)
    {
        $this->persistedMenuItems[] = $menuItem;
    }

    /**
     * @param BaseMenuItem $menuItem
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(BaseMenuItem $menuItem, PreUpdateEventArgs $event)
    {
        $this->updatedMenuItems[] = $menuItem;
    }

    /**
     * @param BaseMenuItem $menuItem
     * @param LifecycleEventArgs $event
     */
    public function preRemove(BaseMenuItem $menuItem, LifecycleEventArgs $event)
    {
        $this->removedMenuItems[] = $menuItem;
    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $uow = $event->getEntityManager()->getUnitOfWork();

        $isNeedRecoverTree = false;

        foreach ($this->persistedMenuItems as $persistedMenuItem) {
            if ($uow->isScheduledForInsert($persistedMenuItem)) {
                $isNeedRecoverTree = true;
            }
        }

        foreach ($this->updatedMenuItems as $updatedMenuItem) {
            if ($uow->isScheduledForUpdate($updatedMenuItem)) {
                $isNeedRecoverTree = true;
            }
        }

        foreach ($this->removedMenuItems as $removedMenuItem) {
            if ($uow->isScheduledForUpdate($removedMenuItem)) {
                $isNeedRecoverTree = true;
            }
        }

        if ($isNeedRecoverTree) {
            $this->recoverTree();
        }

        $this->persistedMenuItems = [];
        $this->updatedMenuItems   = [];
        $this->removedMenuItems   = [];
    }

    protected function recoverTree()
    {
        $this->getMenuItemManager()->getRepository()->recover();
    }
}
