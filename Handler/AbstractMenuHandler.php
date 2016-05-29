<?php

namespace Grossum\MenuBundle\Handler;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

abstract class AbstractMenuHandler implements MenuHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUrlParameters($identifier = null)
    {
        if ($identifier === null) {
            return $this->getListUrlParameters();
        }

        return $this->getEntityUrlParameters($identifier);
    }
}
