<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Entity\TaskAssignment;
use App\Entity\User;
use App\Repository\TaskAssignmentRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Service\TaskEventGeneratorService;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/tasks', name: 'api_tasks_')]
class TaskController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(TaskRepository $taskRepository, SerializerInterface $serializer): JsonResponse
    {
        $tasks = $taskRepository->findAll();

        return $this->json([
            'tasks' => array_map(fn ($task) => [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'isRecurring' => $task->isRecurring(),
                'recurrenceMode' => $task->getRecurrenceMode(),
                'recurrenceRule' => $task->getRecurrenceRule(),
                'rotationUsers' => array_map(fn ($user) => [
                    'id' => $user->getId(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                    'fullName' => $user->getFullName(),
                ], $task->getRotationUsers()->toArray()),
                'rotationCount' => $task->getRotationCount(),
            ], $tasks)
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Task $task, SerializerInterface $serializer): JsonResponse
    {
        $data = $serializer->serialize($task, 'json', ['groups' => ['task:read', 'assignment:read']]);

        return JsonResponse::fromJsonString($data, Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        TaskEventGeneratorService $taskEventGeneratorService
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $task = new Task();
        $task->setTitle($data['title'] ?? '');
        $task->setDescription($data['description'] ?? null);
        $task->setIsRecurring($data['isRecurring'] ?? false);
        $task->setRecurrenceMode($data['recurrenceMode'] ?? 'classic');
        $task->setRecurrenceRule($data['recurrenceRule'] ?? null);

        // rotationCount
        if (isset($data['rotationCount'])) {
            $task->setRotationCount($data['rotationCount']);
        }

        // rotationUsers (IDs zu Entities)
        if (!empty($data['rotationUsers']) && is_array($data['rotationUsers'])) {
            dump($data['rotationUsers']);
            $users = $userRepository->findBy(['id' => $data['rotationUsers']]);
            $task->setRotationUsers(new ArrayCollection($users));
        }

        $task->setCreatedBy($user);
        $em->persist($task);
        $em->flush();

        // Automatische CalendarEvents für Aufgaben (egal ob classic oder per_match)
        $taskEventGeneratorService->generateEvents($task);
        $json = $serializer->serialize($task, 'json', ['groups' => ['task:read']]);

        return JsonResponse::fromJsonString($json, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(
        Task $task,
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        TaskEventGeneratorService $taskEventGeneratorService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }
        if (array_key_exists('description', $data)) {
            $task->setDescription($data['description']);
        }
        if (isset($data['isRecurring'])) {
            $task->setIsRecurring($data['isRecurring']);
        }
        if (array_key_exists('recurrenceMode', $data)) {
            $task->setRecurrenceMode($data['recurrenceMode']);
        }
        if (array_key_exists('recurrenceRule', $data)) {
            $task->setRecurrenceRule($data['recurrenceRule']);
        }
        // rotationCount
        if (array_key_exists('rotationCount', $data)) {
            $task->setRotationCount($data['rotationCount']);
        }
        // rotationUsers (IDs zu Entities)
        if (array_key_exists('rotationUsers', $data) && is_array($data['rotationUsers'])) {
            // If an empty array is provided, clear the rotationUsers collection
            if (0 === count($data['rotationUsers'])) {
                $task->setRotationUsers(new ArrayCollection());
            } else {
                // Doctrine expects an array of ids for an IN query, don't implode them into a string
                $users = $userRepository->findBy(['id' => $data['rotationUsers']]);
                $task->setRotationUsers(new ArrayCollection($users));
            }
        }
        $em->flush();

        // Automatische CalendarEvents für Aufgaben (egal ob classic oder per_match)
        $taskEventGeneratorService->generateEvents($task);
        $json = $serializer->serialize($task, 'json', ['groups' => ['task:read']]);

        return JsonResponse::fromJsonString($json, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Task $task, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($task);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/assignments/{assignmentId}', name: 'assignment_update', methods: ['PUT', 'PATCH'])]
    public function updateAssignment(
        int $assignmentId,
        Request $request,
        TaskAssignmentRepository $assignmentRepo,
        EntityManagerInterface $em,
        SerializerInterface $serializer
    ): JsonResponse {
        $assignment = $assignmentRepo->find($assignmentId);
        if (!$assignment) {
            return new JsonResponse(['error' => 'Assignment not found'], Response::HTTP_NOT_FOUND);
        }
        $data = json_decode($request->getContent(), true);
        if (isset($data['status'])) {
            $assignment->setStatus($data['status']);
        }
        if (isset($data['assignedDate'])) {
            $assignment->setAssignedDate(new DateTimeImmutable($data['assignedDate']));
        }
        $em->flush();
        $json = $serializer->serialize($assignment, 'json', ['groups' => ['assignment:read']]);

        return JsonResponse::fromJsonString($json, Response::HTTP_OK);
    }

    #[Route('/assignments/{assignmentId}', name: 'assignment_delete', methods: ['DELETE'])]
    public function deleteAssignment(int $assignmentId, TaskAssignmentRepository $assignmentRepo, EntityManagerInterface $em): JsonResponse
    {
        $assignment = $assignmentRepo->find($assignmentId);
        if (!$assignment) {
            return new JsonResponse(['error' => 'Assignment not found'], Response::HTTP_NOT_FOUND);
        }
        $em->remove($assignment);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/assignments', name: 'assignments_create', methods: ['POST'])]
    public function createAssignment(Task $task, Request $request, EntityManagerInterface $em, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $assignment = new TaskAssignment();
        $assignment->setTask($task);
        // TODO: User-Entity anhand ID laden
        // $assignment->setUser($user);
        $assignment->setAssignedDate(new DateTimeImmutable($data['assignedDate'] ?? 'now'));
        $assignment->setStatus($data['status'] ?? 'offen');
        $em->persist($assignment);
        $em->flush();
        $json = $serializer->serialize($assignment, 'json', ['groups' => ['assignment:read']]);

        return JsonResponse::fromJsonString($json, Response::HTTP_CREATED);
    }
}
