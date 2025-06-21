<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class EntitySchemaService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getEntitySchema(string $entityClass): array
    {
        /** @var ClassMetadata $meta */
        $meta = $this->em->getClassMetadata($entityClass);

        $fields = [];

        foreach ($meta->getFieldNames() as $field) {
            $fields[$field] = [
                'type' => $meta->getTypeOfField($field),
                'nullable' => $meta->isNullable($field),
                'identifier' => in_array($field, $meta->getIdentifier()),
            ];
        }

        foreach ($meta->getAssociationMappings() as $assocName => $assocMapping) {
            $fields[$assocName] = [
                'type' => 'relation',
                'relationType' => $assocMapping['type'],
                'targetEntity' => $assocMapping['targetEntity'],
                'nullable' => $assocMapping['joinColumns'][0]['nullable'] ?? false,
                'mappedBy' => $assocMapping['mappedBy'] ?? null,
                'inversedBy' => $assocMapping['inversedBy'] ?? null,
                'isCollection' => in_array($assocMapping['type'], [ClassMetadata::ONE_TO_MANY, ClassMetadata::MANY_TO_MANY]),
            ];
        }

        return $fields;
    }
}
