<?php

namespace Grossum\MenuBundle\Form\Type;

use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Grossum\MenuBundle\Form\ChoiceList\EntityIdentifierChoiceLoader;
use Grossum\MenuBundle\Manager\MenuManager;

class MenuEntityIdentifierType extends AbstractType
{
    const NAME = 'grossum_menu_entity_identifier';

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var ChoiceListFactoryInterface
     */
    private $choiceListFactory;

    /**
     * @var IdReader[]
     */
    private $idReaders = [];

    /**
     * @var DoctrineChoiceLoader[]
     */
    private $choiceLoaders = [];

    /**
     * @var MenuManager
     */
    private $menuMaster;

    /**
     * @param ManagerRegistry $registry
     * @param MenuManager $menuMaster
     * @param PropertyAccessorInterface|null $propertyAccessor
     * @param ChoiceListFactoryInterface|null $choiceListFactory
     */
    public function __construct(
        ManagerRegistry $registry,
        MenuManager $menuMaster,
        PropertyAccessorInterface $propertyAccessor = null,
        ChoiceListFactoryInterface $choiceListFactory = null
    ) {
        $this->registry = $registry;
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
        parent::buildForm($builder, $options);

        $builder->addModelTransformer(
            new CallbackTransformer(
                function ($original) use ($options) {
                    if (is_numeric($original) && $options['class']) {
                        /* @var $em EntityManager */
                        $em = $options['em'];

                        return $em->find($options['class'], $original);
                    }

                    return $original;
                },
                function ($reverse) use ($options) {
                    if (is_object($reverse)) {
                        /* @var $idReader IdReader */
                        $idReader = $options['id_reader'];

                        return (string) $idReader->getIdValue($reverse);
                    }

                    return $reverse;
                }
            )
        );
    }

    /**
     * @param Options $options
     * @return null|DoctrineChoiceLoader
     */
    private function createChoiceLoader(Options $options)
    {
        if (null === $options['choices']) {
            if (!$options['class']) {
                return null;
            }

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
    }

    /**
     * The choices are always indexed by ID (see "choices" normalizer
     * and DoctrineChoiceLoader), unless the ID is composite. Then they
     * are indexed by an incrementing integer.
     * Use the ID/incrementing integer as choice value.
     *
     * @param Options $options
     * @return array|null
     */
    private static function choiceValue(Options $options)
    {
        if (!$options['class']) {
            return null;
        }

        /** @var IdReader $idReader */
        $idReader = $options['id_reader'];

        // If the entity has a single-column ID, use that ID as value
        if ($idReader->isSingleId()) {
            return [$idReader, 'getIdValue'];
        }

        // Otherwise, an incrementing integer is used as value automatically
    }

    /**
     * @param Options $options
     * @return array|null
     */
    private static function choiceName(Options $options)
    {
        if (!$options['class']) {
            return null;
        }

        /** @var IdReader $idReader */
        $idReader = $options['id_reader'];

        if ($idReader->isIntId()) {
            return function ($choice, $key, $value) {
                return str_replace('-', '_', (string) $value);
            };
        }
    }

    /**
     * @param mixed $choice
     * @return string
     */
    public static function choiceLabel($choice)
    {
        return (string) $choice;
    }

    /**
     * @param Options $options
     * @return array|null
     */
    public static function createLabelChoicer(Options $options)
    {
        if (!$options['class']) {
            return null;
        }

        return [__CLASS__, 'choiceLabel'];
    }

    /**
     * @param Options $options
     * @return null|IdReader
     */
    private function normalizeIdReader(Options $options)
    {
        if (!$options['class']) {
            return null;
        }

        $hash = CachingFactoryDecorator::generateHash([
            $options['em'],
            $options['class'],
        ]);

        // The ID reader is a utility that is needed to read the object IDs
        // when generating the field values. The callback generating the
        // field values has no access to the object manager or the class
        // of the field, so we store that information in the reader.
        // The reader is cached so that two choice lists for the same class
        // (and hence with the same reader) can successfully be cached.
        if (!isset($this->idReaders[$hash])) {
            $classMetadata = $options['em']->getClassMetadata($options['class']);
            $this->idReaders[$hash] = new IdReader($options['em'], $classMetadata);
        }

        return $this->idReaders[$hash];
    }

    /**
     * @param Options $options
     * @param $em
     * @return ObjectManager|null
     */
    private function normalizeEm(Options $options, $em)
    {
        if (!$options['class']) {
            return $this->registry->getManager();
        }

        if (null !== $em) {
            if ($em instanceof ObjectManager) {
                return $em;
            }

            return $this->registry->getManager($em);
        }

        $em = $this->registry->getManagerForClass($options['class']);

        if (null === $em) {
            throw new RuntimeException(sprintf(
                'Class "%s" seems not to be a managed Doctrine entity. '.
                'Did you forget to map it?',
                $options['class']
            ));
        }

        return $em;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class'                     => null,
            'em'                        => null,
            'choices'                   => null,
            'choice_loader'             => function (Options $options) {
                return $this->createChoiceLoader($options);
            },
            'choice_label'              => function (Options $options) {
                return self::createLabelChoicer($options);
            },
            'choice_name'               => function (Options $options) {
                return self::choiceName($options);
            },
            'choice_value'              => function (Options $options) {
                return self::choiceValue($options);
            },
            'id_reader'                 => null,
            'choice_translation_domain' => false,
        ]);

        $resolver->setNormalizer('em', function (Options $options, $em) {
            return $this->normalizeEm($options, $em);
        });
        $resolver->setNormalizer('id_reader', function (Options $options) {
            return $this->normalizeIdReader($options);
        });

        $resolver->setAllowedTypes('em', ['null', 'string', 'Doctrine\Common\Persistence\ObjectManager']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
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
