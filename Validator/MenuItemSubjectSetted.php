<?php

namespace Grossum\MenuBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class MenuItemSubjectSetted extends Constraint
{
    public $message = 'The menu item "subject" must be the one';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT];
    }
}
