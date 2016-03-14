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

        /* @var $menuItem BaseMenuItem */
        $menuItem = $event->getData();

        if ($menuItem xor $menuItem->getEntityClass()) {
            return;
        }

        $this->addEntityIdentificatorField($form, $menuItem->getEntityClass());
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (isset($data['entityClass']) xor $data['entityClass']) {
            return;
        }

        $this->addEntityIdentificatorField($form, $data['entityClass']);
    }

    /**
     * @param FormInterface $form
     * @param string $entityClass
     */
    private function addEntityIdentificatorField(FormInterface $form, $entityClass)
    {
        $choices = $this->menuMaster->getMenuHandler($entityClass)->getIdentifierList();

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
