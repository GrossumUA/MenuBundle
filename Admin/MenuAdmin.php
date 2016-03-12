<?php

namespace Grossum\MenuBundle\Admin;

use Doctrine\ORM\EntityManager;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

use Grossum\MenuBundle\Entity\BaseMenu;
use Grossum\MenuBundle\Entity\EntityManager\BaseMenuItemManager;

class MenuAdmin extends Admin
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var BaseMenuItemManager
     */
    private $menuItemManager;

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', null, ['label' => 'grossum_menu.admin.menu.name']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, ['label' => 'grossum_menu.admin.menu.name']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, ['edit'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $menuId = $admin->getRequest()->get('id');

        $menu->addChild(
            $this->trans('grossum_menu.admin.side_menu.link_edit_page'),
            [
                'uri' => $admin->generateUrl(
                    'edit',
                    [
                        'id' => $menuId
                    ]
                )
            ]
        );

        $menu->addChild(
            $this->trans('grossum_menu.admin.side_menu.link_items_list'),
            [
                'uri' => $admin->generateUrl(
                    'grossum_menu.admin.menu|grossum_menu.admin.menu_item.list',
                    [
                        'id' => $menuId
                    ]
                )
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        /* @var $object BaseMenu */

        $rootMenuItem = $this->menuItemManager->createEntityInstance();

        $rootMenuItem->setTitle('==Menu==');
        $rootMenuItem->setUrl('==Menu==');
        $rootMenuItem->setMenu($object);

        $this->entityManager->persist($rootMenuItem);
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param BaseMenuItemManager $menuItemManager
     */
    public function setMenuItemManager(BaseMenuItemManager $menuItemManager)
    {
        $this->menuItemManager = $menuItemManager;
    }
}
