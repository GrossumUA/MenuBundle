<?php

namespace Grossum\MenuBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use Grossum\MenuBundle\Form\ChoiceList\EntityIdentifierChoiceLoader;
use Grossum\MenuBundle\Form\DataTransformer\IdToMenuItemTransformer;
use Grossum\MenuBundle\Manager\MenuManager;

class MenuEntityIdentifierType extends AbstractType
{
    const NAME = 'grossum_menu_entity_identifier';

    /**
     * @var ChoiceListFactoryInterface
     */
    private $choiceListFactory;

    /**
     * @var DoctrineChoiceLoader[]
     */
    private $choiceLoaders = [];

    /**
     * @var MenuManager
     */
    private $menuMaster;

    /**
     * @param MenuManager $menuMaster
     * @param PropertyAccessorInterface|null $propertyAccessor
     * @param ChoiceListFactoryInterface|null $choiceListFactory
     */
    public function __construct(
        MenuManager $menuMaster,
        PropertyAccessorInterface $propertyAccessor = null,
        ChoiceListFactoryInterface $choiceListFactory = null
    ) {
        $this->menuMaster = $menuMaster;
        $this->choiceListFactory = $choiceListFactory ?: new CachingFactoryDecorator(
            new PropertyAccessDecorator(
                new DefaultChoiceListFactory(),
                $propertyAccessor
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new IdToMenuItemTransformer($options['em'], $options['class'], $options['id_reader'])
        );
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('choice_loader', function (Options $options) {
            if (null === $options['choices']) {
                $hash = CachingFactoryDecorator::generateHash([
                    $options['em'],
                    $options['class'],
                ]);

                if (isset($this->choiceLoaders[$hash])) {
                    return $this->choiceLoaders[$hash];
                }

                $entityLoader = new EntityIdentifierChoiceLoader($this->menuMaster, $options['em'], $options['class']);

                $doctrineChoiceLoader = new DoctrineChoiceLoader(
                    $this->choiceListFactory,
                    $options['em'],
                    $options['class'],
                    $options['id_reader'],
                    $entityLoader
                );

                if ($hash !== null) {
                    $this->choiceLoaders[$hash] = $doctrineChoiceLoader;
                }

                return $doctrineChoiceLoader;
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
