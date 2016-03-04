<?php

namespace Grossum\MenuBundle\Entity\EntityManager;

use Doctrine\Common\Persistence\ObjectManager;

use Grossum\CoreBundle\Entity\EntityTrait\SaveUpdateInManagerTrait;
use Grossum\MenuBundle\Entity\Repository\BaseMenuRepository;

class BaseMenuManager
{
    use SaveUpdateInManagerTrait;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var BaseMenuRepository
     */
    protected $repository;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     * @param string $class
     */
    public function __construct(ObjectManager $objectManager, $class)
    {
        $this->objectManager = $objectManager;
        $this->class = $class;
    }

    /**
     * @return BaseMenuRepository
     */
    public function getRepository()
    {
        if ($this->repository === null) {
            $this->repository = $this->objectManager->getRepository($this->class);
        }

        return $this->repository;
    }
}
