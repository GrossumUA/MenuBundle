<?php

namespace Grossum\MenuBundle\Manager;

use Grossum\MenuBundle\Handler\MenuHandlerInterface;

class MenuManager
{
    /**
     * @var MenuHandlerInterface[]
     */
    protected $handlers = [];

    /**
     * @param MenuHandlerInterface $menuHandler
     */
    public function addMenuHandler(MenuHandlerInterface $menuHandler)
    {
        $this->handlers[$menuHandler->getIdentifierClass()] = $menuHandler;
    }

    /**
     * @param string $className
     * @return MenuHandlerInterface
     */
    public function getMenuHandler($className)
    {
        $className = (string)$className;
        if (!array_key_exists($className, $this->handlers)) {
            throw new \InvalidArgumentException(sprintf('MenuHandlerInterface is messing for %s', $className));
        }

        return $this->handlers[$className];
    }
}
