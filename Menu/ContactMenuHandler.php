<?php

namespace Grossum\MenuBundle\Menu;

use Grossum\MenuBundle\Handler\AbstractMenuHandler;

class ContactMenuHandler extends AbstractMenuHandler
{
    const IDENTIFIER_CLASS = 'GrossumContactBundle:Contact';

    //TODO: This class only for testing. When we finish we NEED to remove IT;

    /**
     * {@inheritdoc}
     */
    public function generateListUrl()
    {
        return $this->router->generate('admin_grossum_contact_contact_list');
    }

    /**
     * {@inheritdoc}
     */
    public function generateEntityUrl($identifier)
    {
        return $this->router->generate('admin_grossum_contact_contact_edit', ['id' => $identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierClass()
    {
        return self::IDENTIFIER_CLASS;
    }
}
