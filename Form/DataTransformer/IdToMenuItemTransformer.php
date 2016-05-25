<?php

namespace Grossum\MenuBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader;

use Doctrine\Common\Persistence\ObjectManager;

class IdToMenuItemTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var IdReader
     */
    protected $idReader;

    /**
     * @param ObjectManager $manager
     * @param string $class
     * @param IdReader $idReader
     */
    public function __construct(ObjectManager $manager, $class, IdReader $idReader)
    {
        $this->manager  = $manager;
        $this->class    = $class;
        $this->idReader = $idReader;
    }

    /**
     * @param mixed $original
     * @return mixed|object
     */
    public function transform($original)
    {
        if (is_numeric($original)) {
            return $this->manager->find($this->class, $original);
        }

        return $original;
    }

    /**
     * @param mixed $reverse
     * @return mixed|string
     */
    public function reverseTransform($reverse)
    {
        if (is_object($reverse)) {
            return (string) $this->idReader->getIdValue($reverse);
        }

        return $reverse;
    }
}
