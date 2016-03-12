<?php

namespace Grossum\MenuBundle\Menu;

use Doctrine\ORM\EntityManager;

use Grossum\MenuBundle\Handler\AbstractMenuHandler;

class StaticPageMenuHandler extends AbstractMenuHandler
{
    const IDENTIFIER_CLASS = 'Application\Grossum\StaticPageBundle\Entity\StaticPage';

    //TODO: This class only for testing. When we finish we NEED to remove IT;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * {@inheritdoc}
     */
    public function generateListUrl()
    {
        return $this->router->generate('admin_grossum_staticpage_staticpage_list');
    }

    /**
     * {@inheritdoc}
     */
    public function generateEntityUrl($identifier)
    {
        return $this->router->generate('admin_grossum_staticpage_staticpage_edit', ['id' => $identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierClass()
    {
        return self::IDENTIFIER_CLASS;
    }

    public function getIdentifierList()
    {
        $menuItems = $this
            ->entityManager
            ->createQuery('
                SELECT sp
                FROM ApplicationGrossumStaticPageBundle:StaticPage AS sp
                WHERE sp.parent IS NOT NULL
            ')
            ->getResult();

        return $menuItems;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Static page';
    }
}
