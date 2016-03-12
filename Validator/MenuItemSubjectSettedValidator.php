<?php

namespace Grossum\MenuBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Grossum\MenuBundle\Entity\BaseMenuItem;

class MenuItemSubjectSettedValidator extends ConstraintValidator
{
    /**
     * @param BaseMenuItem $menuItem
     * @param Constraint $constraint
     */
    public function validate($menuItem, Constraint $constraint)
    {
        /* @var $menuItem BaseMenuItem */
        /* @var $constraint MenuItemSubjectSetted */

        if (!($menuItem->getUrl() xor $menuItem->getEntityClass())) {
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
