<?php

namespace Grossum\MenuBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;

use Grossum\MenuBundle\Entity\BaseMenuItem;
use Grossum\MenuBundle\Entity\EntityManager\BaseMenuItemManager;

class MenuItemSubscriber implements EventSubscriber
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
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
        ];
    }

    /**
     * We inject service container here because without it we have circular reference for service
     * "doctrine.orm.default_entity_manager" when injecting "grossum_menu.entity.manager.menu_item.manager"
     * manually.
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
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $uow = $event->getEntityManager()->getUnitOfWork();

        $isNeedRecoverTree = false;

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof BaseMenuItem) {
                $isNeedRecoverTree = true;
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof BaseMenuItem) {
                $isNeedRecoverTree = true;
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof BaseMenuItem) {
                $isNeedRecoverTree = true;
            }
        }

        if ($isNeedRecoverTree) {
            $this->recoverTree();
        }
    }

    protected function recoverTree()
    {
        $this->getMenuItemManager()->getRepository()->recover();
    }
}
