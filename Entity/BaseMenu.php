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
     * @var Collection
     */
    protected $menuItems;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->menuItems = new ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return BaseMenu
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return BaseMenu
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return BaseMenu
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() ?: 'New Menu';
    }

    /**
     * Add menuItem
     *
     * @param BaseMenuItem $menuItem
     *
     * @return $this
     */
    public function addMenuItem(BaseMenuItem $menuItem)
    {
        $menuItem->setMenu($this);
        $this->menuItems[] = $menuItem;

        return $this;
    }

    /**
     * Remove menuItem
     *
     * @param BaseMenuItem $menuItem
     */
    public function removeMenuItem(BaseMenuItem $menuItem)
    {
        $this->menuItems->removeElement($menuItem);
    }

    /**
     * Get menuItems
     *
     * @return Collection
     */
    public function getMenuItems()
    {
        return $this->menuItems;
    }
}
