<?php

namespace Grossum\MenuBundle\Admin;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

use Knp\Menu\ItemInterface as MenuItemInterface;

class MenuAdmin extends Admin
{
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

        $menuId = $this->getRequest()->get('id');

        $menu->addChild(
            $this->trans('grossum_menu.admin.side_menu.link_edit_page'),
            [
                'uri' => $this->generateUrl(
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
                'uri' => $this->generateUrl(
                    'grossum_menu.admin.menu|grossum_menu.admin.menu_item.list',
                    [
                        'id' => $menuId
                    ]
                )
            ]
        );
    }
}
