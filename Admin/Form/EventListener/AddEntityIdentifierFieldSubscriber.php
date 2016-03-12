<?php

namespace Grossum\MenuBundle\Admin\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormInterface;

use Grossum\MenuBundle\Entity\BaseMenuItem;
use Grossum\MenuBundle\Manager\MenuManager;

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

        $menuItem = $event->getData();
        /* @var $menuItem BaseMenuItem */

        $entityClass = $menuItem ? $menuItem->getEntityClass() : null;

        $this->addEntityIdentificatorField($form, $entityClass);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $this->addEntityIdentificatorField($form, $data['entityClass']);
    }

    /**
     * @param FormInterface $form
     * @param string|null $entityClass
     */
    private function addEntityIdentificatorField(FormInterface $form, $entityClass = null)
    {
        $choices = $entityClass ? $this->menuMaster->getMenuHandler($entityClass)->getIdentifierList() : [];

        if ($form->has('entityIdentifier')) {
            $form->remove('entityIdentifier');
        }

        $form->add(
            'entityIdentifier',
            'choice',
            [
                'required'    => false,
                'choices'     => $choices,
                'placeholder' => 'grossum_menu.admin.menu_item.placeholder'
            ]
        );
    }
}
