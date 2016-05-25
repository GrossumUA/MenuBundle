<?php

namespace Grossum\MenuBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Grossum\MenuBundle\Entity\BaseMenuItem;
use Grossum\MenuBundle\Form\Type\MenuEntityIdentifierType;
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

        /** @var BaseMenuItem $menuItem */
        $menuItem = $event->getData();

        if (!$menuItem || !$menuItem->getEntityClass()) {
            return;
        }

        $this->modifyForm($form, $menuItem->getEntityClass());
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (empty($data['entityClass'])) {
            return;
        }

        $this->modifyForm($form, $data['entityClass']);
    }

    /**
     * @param FormInterface $form
     * @param string $entityClass
     */
    private function modifyForm(FormInterface $form, $entityClass)
    {
        $entityIdentifier = $form->get('entityIdentifier');
        $options = $entityIdentifier->getConfig()->getOptions();

        $form
            ->add(
                'entityIdentifier',
                MenuEntityIdentifierType::class,
                [
                    'class'       => $entityClass,
                    'required'    => $options['required'],
                    'label'       => $options['label'],
                    'placeholder' => $options['placeholder'],
                ]
            );
    }
}
