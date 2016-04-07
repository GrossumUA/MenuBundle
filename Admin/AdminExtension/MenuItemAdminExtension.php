<?php

namespace Grossum\MenuBundle\Admin\AdminExtension;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Doctrine\ORM\QueryBuilder;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

use Grossum\MenuBundle\Entity\BaseMenuItem;

class MenuItemAdminExtension extends AdminExtension
{
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
     * @param BaseMenuItem $object
     */
    public function alterObject(AdminInterface $admin, $object)
    {
        // Prevent root object editing
        if ($object->getParent() === null) {
            throw new AccessDeniedException();
        }
    }
}
