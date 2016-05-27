<?php

namespace Grossum\MenuBundle\Provider;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;

use Grossum\MenuBundle\Entity\BaseMenu;
use Grossum\MenuBundle\Entity\EntityManager\BaseMenuManager;
use Grossum\MenuBundle\Entity\EntityManager\BaseMenuItemManager;
use Grossum\MenuBundle\Manager\MenuManager;

class MenuProvider implements MenuProviderInterface
{
    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var BaseMenuManager
     */
    protected $menuManager;

    /**
     * @var BaseMenuItemManager
     */
    protected $menuItemManager;

    /**
     * @var MenuManager
     */
    private $menuMaster;

    /**
     * @param FactoryInterface $factory
     * @param BaseMenuManager $menuManager
     * @param BaseMenuItemManager $menuItemManager
     * @param MenuManager $menuMaster
     */
    public function __construct(
        FactoryInterface $factory,
        BaseMenuManager $menuManager,
        BaseMenuItemManager $menuItemManager,
        MenuManager $menuMaster
    ) {
        $this->factory         = $factory;
        $this->menuManager     = $menuManager;
        $this->menuItemManager = $menuItemManager;
        $this->menuMaster      = $menuMaster;
    }

    /**
     * @param $name
     * @return null|BaseMenu
     */
    protected function find($name)
    {
        return $this->menuManager->getRepository()->findOneBy(['name' => $name]);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name, array $options = [])
    {
        $menu = $this->find($name);

        return $menu !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, array $options = [])
    {
        $menuEntity = $this->find($name);

        if ($menuEntity === null) {
            throw new \InvalidArgumentException(sprintf('The menu "%s" is not defined.', $name));
        }

        $repo = $this->menuItemManager->getRepository();

        $rootMenuEntity = $repo->findMenuRootItem($menuEntity);
        $treeNodes      = $repo->getNodesHierarchy($rootMenuEntity);
        $nestedTree     = $repo->buildTreeArray($treeNodes);
        $childrenIndex  = $repo->getChildrenIndex();

        $menu = $this->factory->createItem('root');

        $this->addChildrenToMenuItem($menu, $nestedTree, $childrenIndex);

        return $menu;
    }

    /**
     * @param ItemInterface $parent
     * @param array $tree
     * @param string $childrenIndex
     */
    private function addChildrenToMenuItem(ItemInterface $parent, array $tree, $childrenIndex)
    {
        foreach ($tree as $node) {
            if (null !== $node['url']) {
                $uri = $node['url'];
            } else {
                $uri = $this
                    ->menuMaster
                    ->getMenuHandler($node['entityClass'])
                    ->getUrl($node['entityIdentifier']);
            }

            $child = $parent->addChild($node['title'], ['uri' => $uri]);

            if (count($node[$childrenIndex])) {
                $this->addChildrenToMenuItem($child, $node[$childrenIndex], $childrenIndex);
            }
        }
    }
}
