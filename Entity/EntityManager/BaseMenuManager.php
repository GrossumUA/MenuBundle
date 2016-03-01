<?php

namespace Grossum\MenuBundle\Entity\EntityManager;

use Application\Grossum\MenuBundle\Entity\Repository\MenuRepository;
use Doctrine\Common\Persistence\ObjectManager;

use Grossum\CoreBundle\Entity\EntityTrait\SaveUpdateInManagerTrait;

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

    /** @var  ObjectManager */
    private $objectManager;

    public function __construct(ObjectManager $objectManager, $menuClass)
    {
        $this->objectManager = $objectManager;
        $this->repository    = $objectManager->getRepository('GrossumMenuBundle:BaseMenu');
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
