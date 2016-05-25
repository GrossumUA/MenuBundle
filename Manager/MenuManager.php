<?php

namespace Grossum\MenuBundle\Manager;

use Grossum\MenuBundle\Handler\MenuHandlerInterface;

class MenuManager
{
    /**
     * @var MenuHandlerInterface[]
     */
    private $handlers = [];

    /**
     * @var array [[id1 => title1], ...]
     */
    private $choiceList;

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
        if (!isset($this->handlers[$className])) {
            throw new \InvalidArgumentException(sprintf('MenuHandlerInterface is messing for %s', $className));
        }

        return $this->handlers[$className];
    }

    /**
     * @return array [[id1 => title1], ...]
     */
    public function getChoiceList()
    {
        if ($this->choiceList === null) {
            $this->choiceList = [];

            foreach ($this->handlers as $identifierClass => $handler) {
                $this->choiceList[$identifierClass] = $handler->getTitle();
            }
        }

        return $this->choiceList;
    }
}
