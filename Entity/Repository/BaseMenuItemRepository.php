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
     * @return BaseMenuItem[]
     */
    public function findMenuItems($menuId)
    {
        $qb = $this->createQueryBuilder('menu_item');
        $qb
            ->where($qb->expr()->eq('menu_item.menu', ':menu'))
            ->setParameter('menu', $menuId);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int $menuId
     * @param BaseMenuItem $menuItem
     * @return BaseMenuItem[]
     */
    public function findAvailableMenuItems($menuId, BaseMenuItem $menuItem)
    {
        $qb = $this->createQueryBuilder('menu_item');
        $qb
            ->where($qb->expr()->eq('menu_item.menu', ':menu'))
            ->setParameter('menu', $menuId);

        $lft = $menuItem->getLft();
        $rgt = $menuItem->getRgt();

        if ($lft && $rgt) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->lt('menu_item.lft', $menuItem->getLft()),
                $qb->expr()->gt('menu_item.rgt', $menuItem->getRgt())
            ));
        }

        return $qb->getQuery()->getResult();
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
            ->where($qb->expr()->isNull('menu_item.parent'))
            ->andWhere($qb->expr()->eq('menu_item.menu', ':menu'))
            ->setParameter('menu', $menu);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
