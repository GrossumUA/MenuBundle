<?php

namespace Grossum\MenuBundle\Handler;

interface MenuHandlerInterface
{
    /**
     * Get url parameters for list page
     *
     * @return string
     */
    public function getListUrlParameters();

    /**
     * Get url parameters for entity page
     *
     * @param string $identifier
     * @return string
     */
    public function getEntityUrlParameters($identifier);

    /**
     * Get url parameters
     *
     * @param null|string $identifier
     * @return string
     */
    public function getUrlParameters($identifier = null);

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
