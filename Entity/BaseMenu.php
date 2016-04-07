<?php

namespace Grossum\MenuBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

abstract class BaseMenu
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var Collection|BaseMenuItem[]
     */
    protected $menuItems;

    public function __construct()
    {
        $this->menuItems = new ArrayCollection();
    }

    /**
     * @return integer
     */
    abstract public function getId();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param BaseMenuItem $menuItem
     *
     * @return $this
     */
    public function addMenuItem(BaseMenuItem $menuItem)
    {
        if (!$this->menuItems->contains($menuItem)) {
            $this->menuItems[] = $menuItem;

            $menuItem->setMenu($this);
        }

        return $this;
    }

    /**
     * @param BaseMenuItem $menuItem
     */
    public function removeMenuItem(BaseMenuItem $menuItem)
    {
        $this->menuItems->removeElement($menuItem);
    }

    /**
     * @return Collection|BaseMenuItem[]
     */
    public function getMenuItems()
    {
        return $this->menuItems;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() ?: 'New Menu';
    }
}
