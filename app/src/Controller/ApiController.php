<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\UserRelation;
use App\Repository\AbstractApiRepositoryInterface;
use App\Repository\OptimizedRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\MissingIdentifierField;
use Error;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class ApiController extends AbstractController
{
    protected string $title = '';

    /** @var array<string, mixed> */
    protected array $fillable = [];

    /** @var array<string, mixed> */
    protected array $columns = [];

    /** @var array<string, mixed> */
    protected array $casts = [];

    /** @var array<string, mixed> */
    protected array $types = [];

    protected string $templateDir = '';
    protected bool $createAndEditAllowed = true;

    /** @var class-string */
    protected string $entityClass;
    protected string $entityName = '';
    protected string $entityNamePlural = '';

    /** @var array<string, array{type: int, entityName: string, label_fields?: array<string>}> */
    protected array $relations = [];

    /** @var array<string, array<int>> */
    protected array $relationEntries = [];
    protected string $urlPart = '';

    /** @var array<int, Error> */
    private array $errors = [];
    private string $permissionPrefix = '';

    private int $defaultPage = 1;
    private int $defaultResultsPerPage = 20;

    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
        $this->templateDir = 'api';
        $this->permissionPrefix = $this->convertToSnakeCase($this->entityName);
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $entries = $this->em->getRepository($this->entityClass)->findAll();
        //        $entries = $this->em->createQuery('SELECT * FROM ' . $this->entityClass . ' p');
        $teams = $this->em->getRepository(UserRelation::class)->findRelevantTeams($this->getUser());

//        $entries = [];
        /*
        $repository = $this->em->getRepository($this->entityClass);
        if (class_implements($repository, AbstractApiRepositoryInterface::class)) {
            $entries = $repository->fetchRelevantList($this->getUser());
        } else {
            $entries = $this->em->getRepository($this->entityClass)
                ->createQueryBuilder('p')
                ->setFirstResult($request->page ?? $this->defaultPage)
                ->setMaxResults($request->resultsPerPage ?? $this->defaultResultsPerPage)
                ->getQuery()
                ->getResult();
        }
        */

        return $this->render($this->templateDir . '/index.html.twig', [
            'entries' => $entries,
            'entityName' => $this->entityName,
            'entityNamePlural' => $this->entityNamePlural,
            'createAndEditAllowed' => $this->createAndEditAllowed,
            'urlPart' => $this->convertToSnakeCase($this->entityNamePlural),
        ]);
    }

    #[Route('/api/schema/{entity}', name: 'api_entity_schema')]
    public function getSchema(string $entity, UserInterface $user): JsonResponse
    {
        /** @var class-string $entityClass */
        $entityClass = 'App\\Entity\\' . $entity;

        if (!class_exists($entityClass)) {
            return new JsonResponse(['error' => 'Entity unknown'], 403);
        }

        $meta = $this->em->getClassMetadata($entityClass);

        $schema = [];

        foreach ($meta->fieldMappings as $field => $mapping) {
            if ('id' == $field) {
                continue;
            }

            $type = $mapping['type'];

            $schema[$field] = [
                'type' => $this->mapDoctrineTypeToFormType($type),
            ];
        }

        foreach ($meta->associationMappings as $field => $assoc) {
            /** @var class-string $targetClass */
            $targetClass = $assoc['targetEntity'];
            $repo = $this->em->getRepository($targetClass);

            $entities = $repo instanceof OptimizedRepositoryInterface
                ? $repo->fetchFullList($user)
                : $repo->findAll();

            $choices = array_map(fn ($entity) => ($entity instanceof $targetClass) ? [
                'id' => $entity->getId(),
                'label' => (string) $entity,
            ] : [], $entities);

            $schema[$field] = [
                'multiple' => in_array($assoc['type'], [4, 8]),
                'type' => 'relation',
                'relationType' => $assoc['type'],
                'targetEntity' => $targetClass,
                'choices' => $choices,
            ];
        }

        return $this->json($schema);
    }

    #[Route('/list', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $entries = [];
        /** @var \Doctrine\ORM\EntityRepository<object> $repository */
        $repository = $this->em->getRepository($this->entityClass);

        if ($repository instanceof OptimizedRepositoryInterface) {
            $entries = $repository->fetchFullList($this->getUser());
        } else {
            $entries = $repository->createQueryBuilder('p')
                ->setFirstResult((int) $request->query->get('page', $this->defaultPage))
                ->setMaxResults((int) $request->query->get('resultsPerPage', $this->defaultResultsPerPage))
                ->getQuery()
                ->getResult();
        }

        $context = ['groups' => [$this->permissionPrefix . ':read']];

        $json = $this->serializer->serialize($entries, 'json', $context);

        $data = json_decode($json);
        $json = json_encode(array_merge($data, ['relations' => $this->relations]));

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        /** @var \Doctrine\ORM\EntityRepository<object> $repository */
        $repository = $this->em->getRepository($this->entityClass);
        $entity = $repository instanceof OptimizedRepositoryInterface
            ? $repository->fetchFullEntry($id, $this->getUser())
            : $repository->find($id);

        $context = ['groups' => [lcfirst($this->entityName) . ':read']];
        $json = $this->serializer->serialize($entity, 'json', $context);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $dataArray = json_decode($request->getContent(), true);

            if (!$dataArray) {
                return new JsonResponse(['error' => 'Invalid JSON data'], 400);
            }

            $dataArray = $this->cleanupData($dataArray);
            $dataArray = $this->collectionRelationIds($dataArray);

            $data = json_encode($dataArray);
            $context = ['groups' => [$this->permissionPrefix . ':write']];

            $entity = $this->serializer->deserialize(
                $data,
                $this->entityClass,
                'json',
                $context
            );

            $this->handleRelations($entity);

            $errors = $this->validator->validate($entity);
            $messages = []; // Initialize messages array
            if (
                count($errors) > 0
                || count($this->errors) > 0
            ) {
                foreach ($errors as $error) {
                    $messages[] = $error->getMessage();
                }

                foreach ($this->errors as $error) {
                    $messages[] = $error->getMessage();
                }

                return new JsonResponse(
                    ['error' => implode(' ', $messages)],
                    400
                );
            }

            $this->em->persist($entity);
            $this->em->flush();

            $context = ['groups' => [lcfirst($this->entityName) . ':read']];
            $json = $this->serializer->serialize($entity, 'json', $context);

            return new JsonResponse($json, 201, [], true);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
        /** @var \Doctrine\ORM\EntityRepository<object> $repository */
        $repository = $this->em->getRepository($this->entityClass);
        $entity = $repository->find($id);
        $dataArray = json_decode($request->getContent(), true);

        $dataArray = $this->cleanupData($dataArray);
        $dataArray = $this->collectionRelationIds($dataArray);

        $data = json_encode($dataArray);

        $this->serializer->deserialize(
            $data,
            $this->entityClass,
            'json',
            ['object_to_populate' => $entity],
        );

        $this->handleRelations($entity);

        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse(['error' => $errorsString], 400);
        }

        $this->em->persist($entity);
        $this->em->flush();

        $context = ['groups' => [lcfirst($this->entityName) . ':read']];
        $json = $this->serializer->serialize($entity, 'json', $context);

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $entity = $this->em->getRepository($this->entityClass)->find($id);
        $this->em->remove($entity);
        $this->em->flush();

        return new JsonResponse(null, 204);
    }

    private function mapDoctrineTypeToFormType(string $doctrineType): string
    {
        return match ($doctrineType) {
            'string' => 'text',
            'integer', 'bigint', 'smallint' => 'number',
            'boolean' => 'checkbox',
            'date' => 'date',
            'datetime', 'datetimetz' => 'datetime-local',
            default => 'text',
        };
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function cleanupData(array $data): array
    {
        $meta = $this->em->getClassMetadata($this->entityClass);

        foreach ($meta->fieldMappings as $field => $mapping) {
            if (isset($data[$field]) && in_array($mapping['type'], ['date', 'datetime']) && empty($data[$field])) {
                unset($data[$field]);
            } elseif (in_array($field, ['latitude', 'longitude']) && is_string($data[$field])) {
                $data[$field] = floatval($data[$field]);
            }
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $dataArray
     *
     * @return array<string, mixed>
     */
    private function collectionRelationIds(array $dataArray): array
    {
        foreach ($this->relations as $relation => $config) {
            $this->relationEntries[$relation] = [];
            if (!empty($dataArray[$relation])) {
                if (in_array($config['type'], [4, 8]) && is_array($dataArray[$relation])) {
                    $this->relationEntries[$relation] = array_map('intval', $dataArray[$relation]);
                } elseif (in_array($config['type'], [2])) {
                    $this->relationEntries[$relation] = (int) $dataArray[$relation];
                }
            }
            unset($dataArray[$relation]);
        }

        return $dataArray;
    }

    private function handleRelations(object $entity): self
    {
        foreach ($this->relations as $relation => $config) {
            if (in_array($config['type'], [4, 8])) {
                $this->handleMultipleRelation($relation, $config, $entity);
            } else {
                $this->handleSingleRelation($relation, $config, $entity);
            }
        }

        return $this;
    }

    /**
     * @param array{type: int, entityName: string, methodName?: string} $config
     */
    private function handleMultipleRelation(string $relation, array $config, object $entity): self
    {
        // Nur wenn tatsächlich neue Daten für diese Relation vorhanden sind
        if (!isset($this->relationEntries[$relation])) {
            return $this;
        }

        $entityName = $config['entityName'];
        $methodName = $config['methodName'] ?? $entityName;

        foreach ($entity->{'get' . ucfirst($relation)}() as $entry) {
            $entity->{'remove' . $methodName}($entry);
        }

        if (!empty($this->relationEntries[$relation])) {
            /** @var class-string $className */
            $className = sprintf('\App\Entity\%s', $entityName);
            $repository = $this->em->getRepository($className);
            $entries = $repository->findBy(['id' => $this->relationEntries[$relation]]);

            foreach ($entries as $entry) {
                $entity->{'add' . $methodName}($entry);
            }
        }

        return $this;
    }

    /**
     * @param array{type: int, entityName: string, methodName?: string, fieldName?: string} $config
     */
    private function handleSingleRelation(string $relation, array $config, object $entity): self
    {
        $entityName = ucfirst($config['entityName']);
        $methodName = $config['methodName'] ?? $entityName;
        $fieldName = isset($config['fieldName']) ? ucfirst($config['fieldName']) : $entityName;
        // Prüfe ob Relation required ist
        $meta = $this->em->getClassMetadata($this->entityClass);
        $isNullable = $meta->associationMappings[$relation]['joinColumns'][0]['nullable'] ?? false;

        if (!isset($this->relationEntries[$relation]) && !$isNullable) {
            $this->errors[] = new Error(sprintf('%s darf nicht leer sein', $fieldName));

            return $this;
        }

        if (isset($this->relationEntries[$relation])) {
            /** @var class-string $className */
            $className = sprintf('\App\Entity\%s', $entityName);
            try {
                $repository = $this->em->getRepository($className);
                $entry = $repository->find($this->relationEntries[$relation]);
                if ($entry instanceof $className) {
                    $entity->{'set' . $methodName}($entry);
                } else {
                    throw new Exception(sprintf('Relation %s not found', $relation));
                }
            } catch (MissingIdentifierField $exception) {
                $this->errors[] = new Error($fieldName . ' darf nicht leer sein!');
            }
        }

        return $this;
    }

    private function convertToSnakeCase(string $camelCase): string
    {
        $snake = preg_replace('/([a-z])([A-Z])/', '$1_$2', $camelCase);

        return strtolower($snake);
    }
}
