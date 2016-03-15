<?php

namespace Grossum\MenuBundle\Admin\AdminExtension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;

use Grossum\MenuBundle\Entity\BaseMenu;
use Grossum\MenuBundle\Entity\EntityManager\BaseMenuItemManager;

class MenuAdminExtension extends AdminExtension
{
    /**
     * @var BaseMenuItemManager
     */
    protected $menuItemManager;

    /**
     * @param BaseMenuItemManager $menuItemManager
     */
    public function __construct(BaseMenuItemManager $menuItemManager)
    {
        $this->menuItemManager = $menuItemManager;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(AdminInterface $admin, $object)
    {
        /* @var $object BaseMenu */
        $this->menuItemManager->createRootMenuItemForMenu($object);
    }
}
