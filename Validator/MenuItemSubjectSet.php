<?php

namespace Grossum\MenuBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class MenuItemSubjectSet extends Constraint
{
    public $message = 'In menu item must be set "url" or "entity class"';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT];
    }
}
