<?php

namespace Grossum\MenuBundle\Admin\AdminExtension;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Doctrine\ORM\QueryBuilder;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

use Grossum\MenuBundle\Entity\BaseMenuItem;
use Grossum\MenuBundle\Entity\EntityManager\BaseMenuItemManager;

class MenuItemAdminExtension extends AdminExtension
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
     * @param QueryBuilder $query
     */
    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list')
    {
        $query->andWhere($query->getRootAliases()[0] . '.parent IS NOT NULL');
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(AdminInterface $admin, $object)
    {
        $this->recoverTree();
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(AdminInterface $admin, $object)
    {
        $this->recoverTree();
    }

    /**
     * {@inheritdoc}
     * @param BaseMenuItem $object
     */
    public function alterObject(AdminInterface $admin, $object)
    {
        // Prevent root object editing
        if ($object->getParent() === null) {
            throw new AccessDeniedException();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove(AdminInterface $admin, $object)
    {
        $this->recoverTree();
    }

    protected function recoverTree()
    {
        $this->menuItemManager->getRepository()->recover();
    }
}
