<?php

namespace Grossum\MenuBundle\Handler;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

abstract class AbstractMenuHandler implements MenuHandlerInterface
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($identifier = null)
    {
        if ($identifier === null) {
            return $this->generateListUrl();
        }

        return $this->generateEntityUrl($identifier);
    }
}
