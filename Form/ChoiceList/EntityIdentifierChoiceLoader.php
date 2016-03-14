<?php

namespace Grossum\MenuBundle\Form\ChoiceList;

use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;

use Doctrine\ORM\EntityManager;

use Grossum\MenuBundle\Manager\MenuManager;
use Grossum\MenuBundle\Handler\MenuHandlerInterface;

class EntityIdentifierChoiceLoader extends ORMQueryBuilderLoader
{
    /**
     * @var MenuHandlerInterface
     */
    private $menuHandler;

    /**
     * @param MenuManager $menuManager
     * @param EntityManager $objectManager
     * @param string $class
     */
    public function __construct(MenuManager $menuManager, EntityManager $objectManager, $class)
    {
        $this->menuHandler = $menuManager->getMenuHandler($class);

        $queryBuilder = $objectManager->createQueryBuilder()
            ->select('alias')
            ->from($class, 'alias');

        parent::__construct($queryBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntities()
    {
        return $this->menuHandler->getIdentifierList();
    }
}
