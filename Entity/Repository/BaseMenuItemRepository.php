<?php

namespace Grossum\MenuBundle\Entity\Repository;

use Doctrine\ORM\NonUniqueResultException;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

use Grossum\MenuBundle\Entity\BaseMenuItem;
use Grossum\MenuBundle\Entity\BaseMenu;

abstract class BaseMenuItemRepository extends NestedTreeRepository
{
    /**
     * @param int $menuId
     * @param BaseMenuItem[] $except
     * @return BaseMenuItem[]
     */
    public function findMenuItemsExcept($menuId, array $except)
    {
        $qb = $this->createQueryBuilder('menu_item');
        $qb
            ->where(
                $qb->expr()->eq('menu_item.menu', ':menu')
            )
            ->andWhere(
                $qb->expr()->notIn('menu_item.id', ':except')
            )
            ->setParameter('menu', $menuId)
            ->setParameter(
                'except',
                array_map(
                    function ($menuItem) {
                        /* @var $menuItem BaseMenuItem */
                        return $menuItem->getId();
                    },
                    $except
                )
            );

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $menuId
     * @return BaseMenuItem[]
     */
    public function findMenuItems($menuId)
    {
        $qb = $this->createQueryBuilder('menu_item');
        $qb
            ->where(
                $qb->expr()->eq('menu_item.menu', ':menu')
            )
            ->setParameter('menu', $menuId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $menuId
     * @param BaseMenuItem $entity
     * @return BaseMenuItem[]
     */
    public function findAvailableMenuItems($menuId, $entity)
    {
        if (!$entity->getId()) {
            return $this->findMenuItems($menuId);
        }

        // @todo: может вместо получения списка исключения, использовать lft, rgt ???

        $exceptThis = $this->getChildren($entity);
        $exceptThis[] = $entity;

        return $this->findMenuItemsExcept($menuId, $exceptThis);
    }

    /**
     * @param BaseMenu $menu
     * @return BaseMenuItem
     * @throws NonUniqueResultException
     */
    public function findMenuRootItem(BaseMenu $menu)
    {
        $qb = $this->createQueryBuilder('menu_item');
        $qb
            ->where(
                $qb->expr()->isNull('menu_item.parent')
            )
            ->andWhere(
                $qb->expr()->eq('menu_item.menu', ':menu')
            )
            ->setParameter('menu', $menu);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
