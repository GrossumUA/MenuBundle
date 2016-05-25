<?php

namespace Grossum\MenuBundle\Admin;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Doctrine\ORM\QueryBuilder;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

use Grossum\MenuBundle\Entity\BaseMenuItem;
use Grossum\MenuBundle\Entity\EntityManager\BaseMenuItemManager;
use Grossum\MenuBundle\Form\Type\MenuEntityClassType;
use Grossum\MenuBundle\Form\EventListener\AddEntityIdentifierFieldSubscriber;
use Grossum\MenuBundle\Manager\MenuManager;

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
    protected $menuManager;

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
            ->add('url', null, [
                'required' => false,
                'label'    => 'grossum_menu.admin.menu_item.url'
            ])
            ->add('parent', null, [
                'required' => true,
                'label'    => 'grossum_menu.admin.menu_item.parent',
                'choices'  => $this
                    ->menuItemManager
                    ->getRepository()
                    ->findAvailableMenuItems($menuId, $this->getSubject())
            ])
            ->add('entityClass', MenuEntityClassType::class, [
                'required'    => false,
                'label'       => 'grossum_menu.admin.menu_item.entity_class',
                'placeholder' => 'grossum_menu.admin.menu_item.entity_class_placeholder',
            ])
            ->add('entityIdentifier', ChoiceType::class, [
                'required'    => false,
                'label'       => 'grossum_menu.admin.menu_item.entity_identifier',
                'placeholder' => 'grossum_menu.admin.menu_item.placeholder',
            ]);

        $formMapper
            ->getFormBuilder()
            ->addEventSubscriber(new AddEntityIdentifierFieldSubscriber($this->menuManager));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, ['label' => 'grossum_menu.admin.menu_item.title'])
            ->add('parent.title', null, [
                'label' => 'grossum_menu.admin.menu_item.parent',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        /* @var $query QueryBuilder */
        $query->andWhere($query->expr()->isNotNull($query->getRootAliases()[0] . '.parent'));

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function getObject($id)
    {
        /** @var BaseMenuItem $object */
        $object = parent::getObject($id);

        if ($object->getParent() === null) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @param BaseMenuItemManager $menuItemManager
     */
    public function setMenuItemManager(BaseMenuItemManager $menuItemManager)
    {
        $this->menuItemManager = $menuItemManager;
    }

    /**
     * @param MenuManager $menuManager
     */
    public function setMenuManager(MenuManager $menuManager)
    {
        $this->menuManager = $menuManager;
    }
}
