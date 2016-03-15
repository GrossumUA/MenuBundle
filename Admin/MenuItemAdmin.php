<?php

namespace Grossum\MenuBundle\Admin;

use Grossum\MenuBundle\Form\Type\MenuEntityIdentifierType;
use Grossum\MenuBundle\Form\Type\MenuEntityClassType;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

use Grossum\MenuBundle\Entity\EntityManager\BaseMenuItemManager;
use Grossum\MenuBundle\Manager\MenuManager;
use Grossum\MenuBundle\Form\EventListener\AddEntityIdentifierFieldSubscriber;

class MenuItemAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected $parentAssociationMapping = 'menu';

    /**
     * @var BaseMenuItemManager
     */
    protected $menuItemManager;

    /**
     * @var MenuManager
     */
    protected $menuMaster;

    /**
     * {@inheritdoc}
     */
    public function configureRoutes(RouteCollection $routes)
    {
        $routes
            ->add('tree', 'tree')
            ->add('save-tree', 'save-tree')
            ->add('get-entity-identifiers-by-entity-class', 'get-entity-identifiers-by-entity-class');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $menuId = $this->request->get($this->getParent()->getIdParameter());

        $formMapper
            ->add('title', null, ['label' => 'grossum_menu.admin.menu_item.title'])
            ->add(
                'url',
                null,
                [
                    'required' => false,
                    'label'    => 'grossum_menu.admin.menu_item.url'
                ]
            )
            ->add(
                'parent',
                null,
                [
                    'required' => true,
                    'label'    => 'grossum_menu.admin.menu_item.parent',
                    'choices'  => $this
                        ->menuItemManager
                        ->getRepository()
                        ->findAvailableMenuItems($menuId, $this->getSubject())
                ]
            )
            ->add(
                'entityClass',
                MenuEntityClassType::class,
                [
                    'required'    => false,
                    'label'       => 'grossum_menu.admin.menu_item.entity_class',
                    'placeholder' => 'grossum_menu.admin.menu_item.entity_class_placeholder',
                ]
            )
            ->add(
                'entityIdentifier',
                MenuEntityIdentifierType::class
            );

        $formMapper
            ->getFormBuilder()
            ->addEventSubscriber(new AddEntityIdentifierFieldSubscriber($this->menuMaster));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, ['label' => 'grossum_menu.admin.menu_item.title'])
            ->add(
                'parent.title',
                null,
                [
                    'label' => 'grossum_menu.admin.menu_item.parent',
                ]
            );
    }

    /**
     * @param BaseMenuItemManager $menuItemManager
     */
    public function setMenuItemManager(BaseMenuItemManager $menuItemManager)
    {
        $this->menuItemManager = $menuItemManager;
    }

    /**
     * @param MenuManager $menuMaster
     */
    public function setMenuMaster(MenuManager $menuMaster)
    {
        $this->menuMaster = $menuMaster;
    }
}
