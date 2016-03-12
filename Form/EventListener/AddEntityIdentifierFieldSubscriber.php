<?php

namespace Grossum\MenuBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Grossum\MenuBundle\Entity\BaseMenuItem;
use Grossum\MenuBundle\Manager\MenuManager;
use Grossum\MenuBundle\Form\Type\MenuEntityIdentifierType;

class AddEntityIdentifierFieldSubscriber implements EventSubscriberInterface
{
    /**
     * @var MenuManager
     */
    private $menuMaster;

    /**
     * @param MenuManager $menuMaster
     */
    public function __construct(MenuManager $menuMaster)
    {
        $this->menuMaster = $menuMaster;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT   => 'onPreSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();

        /* @var $menuItem BaseMenuItem */
        $menuItem = $event->getData();

        $entityClass = $menuItem ? $menuItem->getEntityClass() : null;

        $this->modifyForm($form, $entityClass);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $this->modifyForm($form, $data['entityClass']);
    }

    /**
     * @param FormInterface $form
     * @param string|null $entityClass
     */
    private function modifyForm(FormInterface $form, $entityClass = null)
    {
        if ($form->has('entityIdentifier')) {
            $form->remove('entityIdentifier');
        }

        $form->add(
            'entityIdentifier',
            MenuEntityIdentifierType::class,
            [
                'required'    => false,
                'class'       => $entityClass,
                'label'       => 'grossum_menu.admin.menu_item.entity_identifier',
                'placeholder' => 'grossum_menu.admin.menu_item.placeholder',
            ]
        );
    }
}
