<?php

namespace Grossum\MenuBundle\Entity\EntityManager;

use Doctrine\Common\Persistence\ObjectManager;

use Grossum\CoreBundle\Entity\EntityTrait\SaveUpdateInManagerTrait;

use Application\Grossum\MenuBundle\Entity\Repository\MenuRepository;

class BaseMenuManager
{
    use SaveUpdateInManagerTrait;

    /**
     * @var string
     */
    private $menuClass;

    /**
     * @var MenuRepository
     */
    private $repository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param ObjectManager $objectManager
     * @param string $menuClass
     */
    public function __construct(ObjectManager $objectManager, $menuClass)
    {
        $this->objectManager = $objectManager;
        $this->menuClass = $menuClass;
    }

    /**
     * @return MenuRepository
     */
    public function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = $this->objectManager->getRepository($this->menuClass);
        }

        return $this->repository;
    }
}
