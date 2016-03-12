<?php

namespace Grossum\MenuBundle\Form\ChoiceList;

use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\DBAL\Connection;

use Grossum\MenuBundle\Manager\MenuManager;

class EntityIdentifierChoiceLoader implements EntityLoaderInterface
{
    /**
     * @var MenuManager
     */
    private $menuManager;

    /**
     * @var EntityManager
     */
    private $objectManager;

    /**
     * @var string
     */
    private $class;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @param MenuManager $menuManager
     * @param EntityManager $objectManager
     * @param string $class
     */
    public function __construct(MenuManager $menuManager, EntityManager $objectManager, $class)
    {
        $this->menuManager   = $menuManager;
        $this->objectManager = $objectManager;
        $this->class         = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntities()
    {
        return $this->menuManager->getMenuHandler($this->class)->getIdentifierList();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntitiesByIds($identifier, array $values)
    {
        if (null === $this->queryBuilder) {
            $this->queryBuilder = $this->objectManager->createQueryBuilder()
                ->select('alias')
                ->from($this->class, 'alias');
        }

        $qb        = clone $this->queryBuilder;
        $alias     = current($qb->getRootAliases());
        $parameter = 'EntityIdentifierChoiceLoader_getEntitiesByIds_'.$identifier;
        $parameter = str_replace('.', '_', $parameter);
        $where     = $qb->expr()->in($alias.'.'.$identifier, ':'.$parameter);

        // Guess type
        $entity = current($qb->getRootEntities());
        $metadata = $qb->getEntityManager()->getClassMetadata($entity);
        if (in_array($metadata->getTypeOfField($identifier), ['integer', 'bigint', 'smallint'])) {
            $parameterType = Connection::PARAM_INT_ARRAY;

            // Filter out non-integer values (e.g. ""). If we don't, some
            // databases such as PostgreSQL fail.
            $values = array_values(array_filter($values, function ($v) {
                return (string) $v === (string) (int) $v;
            }));
        } else {
            $parameterType = Connection::PARAM_STR_ARRAY;
        }
        if (!$values) {
            return [];
        }

        return $qb->andWhere($where)
                  ->getQuery()
                  ->setParameter($parameter, $values, $parameterType)
                  ->getResult();
    }
}
