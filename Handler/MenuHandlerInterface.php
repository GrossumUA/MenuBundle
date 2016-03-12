<?php

namespace Grossum\MenuBundle\Handler;

interface MenuHandlerInterface
{
    /**
     * Generate url for list page
     *
     * @return string
     */
    public function generateListUrl();

    /**
     * Generate url for entity using identifier
     *
     * @param string $identifier
     * @return string
     */
    public function generateEntityUrl($identifier);

    /**
     * Get Url
     *
     * @param null|string $identifier
     * @return string
     */
    public function getUrl($identifier = null);

    /**
     * Get entity class (short form) for this handler, which will be a key in handlers store
     *
     * @return string
     */
    public function getIdentifierClass();

    /**
     * @return array Doctrine-entity-array
     */
    public function getIdentifierList();

    /**
     * @return string
     */
    public function getTitle();
}
