<?php

namespace Grossum\MenuBundle\Admin\AdminExtension;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;

use Grossum\MenuBundle\Entity\BaseMenu;
use Grossum\MenuBundle\Entity\EntityManager\BaseMenuItemManager;

use Doctrine\ORM\EntityManager;

class MenuAdminExtension extends AdminExtension
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var BaseMenuItemManager
     */
    protected $menuItemManager;

    /**
     * @param BaseMenuItemManager $menuItemManager
     * @param EntityManager $entityManager
     */
    public function __construct(BaseMenuItemManager $menuItemManager, EntityManager $entityManager)
    {
        $this->menuItemManager = $menuItemManager;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(AdminInterface $admin, $object)
    {
        /* @var $object BaseMenu */

        $rootMenuItem = $this->menuItemManager->createEntityInstance();

        // @todo: if site is multi language so add translations for other languages
        $rootMenuItem->setTitle('==MENU==');
        $rootMenuItem->setUrl('==MENU==');
        $rootMenuItem->setMenu($object);

        $this->entityManager->persist($rootMenuItem);
    }
}
