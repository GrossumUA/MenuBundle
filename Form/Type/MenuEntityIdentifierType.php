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
     * Creates the label for a choice.
     *
     * @param object $choice The object.
     *
     * @return string The string representation of the object.
     */
    public static function createChoiceLabel($choice)
    {
        return (string) $choice;
    }

    /**
     * Creates the field name for a choice.
     *
     * This method is used to generate field names if the underlying object has
     * a single-column integer ID. In that case, the value of the field is
     * the ID of the object. That ID is also used as field name.
     *
     * @param object     $choice The object.
     * @param int|string $key    The choice key.
     * @param string     $value  The choice value. Corresponds to the object's ID here.
     *
     * @return string The field name.
     *
     * @internal This method is public to be usable as callback. It should not be used in user code.
     */
    public static function createChoiceName($choice, $key, $value)
    {
        return str_replace('-', '_', (string) $value);
    }

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
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $choiceLoader = function (Options $options) {
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
        };

        $choiceLabel = function (Options $options) {
            if (!$options['class']) {
                return null;
            }

            return [__CLASS__, 'createChoiceLabel'];
        };

        $choiceName = function (Options $options) {
            if (!$options['class']) {
                return null;
            }

            /** @var IdReader $idReader */
            $idReader = $options['id_reader'];

            if ($idReader->isIntId()) {
                return [__CLASS__, 'createChoiceName'];
            }
        };

        // The choices are always indexed by ID (see "choices" normalizer
        // and DoctrineChoiceLoader), unless the ID is composite. Then they
        // are indexed by an incrementing integer.
        // Use the ID/incrementing integer as choice value.
        $choiceValue = function (Options $options) {
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
        };

        $emNormalizer = function (Options $options, $em) {
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
        };

        // Set the "id_reader" option via the normalizer. This option is not
        // supposed to be set by the user.
        $idReaderNormalizer = function (Options $options) {
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
        };

        $resolver->setDefaults([
            'class'                     => null,
            'em'                        => null,
            'choices'                   => null,
            'choices_as_values'         => true,
            'choice_loader'             => $choiceLoader,
            'choice_label'              => $choiceLabel,
            'choice_name'               => $choiceName,
            'choice_value'              => $choiceValue,
            'id_reader'                 => null,
            'choice_translation_domain' => false,
        ]);

        $resolver->setNormalizer('em', $emNormalizer);
        $resolver->setNormalizer('id_reader', $idReaderNormalizer);

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
