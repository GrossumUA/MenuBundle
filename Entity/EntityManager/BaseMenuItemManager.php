<?php

namespace Grossum\MenuBundle\Entity\EntityManager;

use Doctrine\ORM\EntityManager;

use Grossum\CoreBundle\Entity\EntityTrait\SaveUpdateInManagerTrait;
use Grossum\MenuBundle\Entity\BaseMenuItem;
use Grossum\MenuBundle\Entity\BaseMenu;
use Grossum\MenuBundle\Entity\Repository\BaseMenuItemRepository;

class BaseMenuItemManager
{
    use SaveUpdateInManagerTrait;

    /**
     * @var string
     */
    private $menuItemClass;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var BaseMenuItemRepository
     */
    private $repository;

    /**
     * @param EntityManager $entityManager
     * @param string $menuItemClass
     */
    public function __construct(EntityManager $entityManager, $menuItemClass)
    {
        $this->entityManager = $entityManager;
        $this->menuItemClass = $menuItemClass;
    }

    /**
     * @return BaseMenuItemRepository
     */
    public function getRepository()
    {
        if (null === $this->repository) {
            $this->repository = $this->entityManager->getRepository($this->menuItemClass);
        }

        return $this->repository;
    }

    /**
     * @param BaseMenu $menu
     * @return BaseMenuItem
     */
    public function createRootMenuItemForMenu(BaseMenu $menu)
    {
        /* @var $rootMenuItem BaseMenuItem */
        $rootMenuItem = new $this->menuItemClass();

        // @todo: if site is multi language so add translations for other languages
        $rootMenuItem->setTitle(BaseMenuItem::ROOT);
        $rootMenuItem->setUrl(BaseMenuItem::ROOT);
        $rootMenuItem->setMenu($menu);

        $this->entityManager->persist($rootMenuItem);

        return $rootMenuItem;
    }

    /**
     * @param array $tree
     * @param BaseMenu $menu
     * @return array|bool
     */
    public function updateAndVerifyTree(array $tree, BaseMenu $menu)
    {
        $root = $this->getRepository()->findMenuRootItem($menu);

        foreach ($tree as $treeData) {
            if (isset($treeData['item_id']) && $treeData['item_id'] === BaseMenuItem::ROOT) {
                continue;
            }

            if (!isset($treeData['parent_id'], $treeData['id'])) {
                continue;
            }

            /* @var BaseMenuItem $menuItem */
            $menuItem = $this->getRepository()->find($treeData['id']);
            $parentId = ($treeData['parent_id'] === BaseMenuItem::ROOT) ? $root->getId() : $treeData['parent_id'];
            $parentMenuItem = $this->getRepository()->find($parentId);

            $menuItem
                ->setParent($parentMenuItem)
                ->setLft($treeData['left'])
                ->setRgt($treeData['right']);
        }

        return $this->getRepository()->verify();
    }

    /**
     * @throws \Exception
     */
    public function flush()
    {
        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
