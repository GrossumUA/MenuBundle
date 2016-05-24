<?php

namespace Grossum\MenuBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

abstract class BaseMenuItem
{
    const ROOT = 'root';

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var string
     */
    protected $entityIdentifier;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var BaseMenu
     */
    protected $menu;

    /**
     * @var int
     */
    protected $lft;

    /**
     * @var int
     */
    protected $rgt;

    /**
     * @var int
     */
    protected $lvl;

    /**
     * @var Collection|BaseMenuItem[]
     */
    protected $children;

    /**
     * @var BaseMenuItem
     */
    protected $parent;

    /**
     * @var BaseMenuItem
     */
    protected $root;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->url = '#';
    }

    /**
     * @return int
     */
    abstract public function getId();

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $entityClass
     *
     * @return $this
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param string $entityIdentifier
     *
     * @return $this
     */
    public function setEntityIdentifier($entityIdentifier)
    {
        $this->entityIdentifier = $entityIdentifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityIdentifier()
    {
        return $this->entityIdentifier;
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
     * @param BaseMenu $menu
     *
     * @return $this
     */
    public function setMenu(BaseMenu $menu)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * @return BaseMenu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @param int $lft
     *
     * @return $this
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * @return int
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @param int $rgt
     *
     * @return $this
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * @return int
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * @param int $lvl
     *
     * @return $this
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * @return int
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * @param BaseMenuItem $child
     *
     * @return $this
     */
    public function addChild(BaseMenuItem $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * @param BaseMenuItem $child
     */
    public function removeChild(BaseMenuItem $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * @return Collection|BaseMenuItem[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param BaseMenuItem $root
     *
     * @return $this
     */
    public function setRoot(BaseMenuItem $root = null)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @return BaseMenuItem
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @param BaseMenuItem $parent
     *
     * @return $this
     */
    public function setParent(BaseMenuItem $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return BaseMenuItem
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->title ?: 'Menu Item';
    }
}
