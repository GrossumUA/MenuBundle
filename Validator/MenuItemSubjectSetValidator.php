<?php

namespace Grossum\MenuBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Grossum\MenuBundle\Entity\BaseMenuItem;

class MenuItemSubjectSetValidator extends ConstraintValidator
{
    /**
     * @param BaseMenuItem $menuItem
     * @param MenuItemSubjectSet $constraint MenuItemSubjectSet is type of Constraint
     */
    public function validate($menuItem, Constraint $constraint)
    {
        $urlWasSet         = $menuItem->getUrl();
        $entityClassWasSet = $menuItem->getEntityClass();

        // must be set only the one
        if (!($urlWasSet xor $entityClassWasSet)) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('url')
                ->addViolation();

            $this->context
                ->buildViolation($constraint->message)
                ->atPath('entityClass')
                ->addViolation();
        }
    }
}
