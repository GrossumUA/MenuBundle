<?php

namespace Grossum\MenuBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class MenuItemSubjectSet extends Constraint
{
    /**
     * @var string
     */
    public $message = 'Menu item should have url or selected entity only';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT];
    }
}
