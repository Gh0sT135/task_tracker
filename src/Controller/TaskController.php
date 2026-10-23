<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateTaskRequest;
use App\Entity\Task;
use App\Enum\TaskStatus;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/tasks', name: 'task_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $tasks = $this->taskRepository->findBy([], ['id' => 'ASC']);

        return new JsonResponse([
            'status' => 'success',
            'tasks' => array_map($this->normalizeTask(...), $tasks),
        ]);
    }

    #[Route('/api/tasks/search', name: 'task_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = (string) $request->query->get('query', '');
        $statusValue = $request->query->get('status');
        $projectId = $request->query->get('project_id');

        $status = null;
        if (null !== $statusValue && '' !== $statusValue) {
            $status = TaskStatus::tryFrom((string) $statusValue);
            if (null === $status) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => sprintf('Недопустимый статус. Допустимые значения: %s', implode(', ', TaskStatus::values())),
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $tasks = $this->taskRepository->search(
            $query,
            $status,
            null !== $projectId && ctype_digit((string) $projectId) ? (int) $projectId : null,
        );

        return new JsonResponse([
            'status' => 'success',
            'search_term' => $query,
            'results' => array_map($this->normalizeTask(...), $tasks),
        ]);
    }

    #[Route(
        '/api/tasks/{id}',
        name: 'task_show',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    public function show(int $id): JsonResponse
    {
        $task = $this->taskRepository->find($id);

        if (null === $task) {
            throw $this->createNotFoundException('Задача не найдена');
        }

        return new JsonResponse([
            'status' => 'success',
            'task' => $this->normalizeTask($task),
        ]);
    }

    #[Route('/api/tasks', name: 'task_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateTaskRequest $payload): JsonResponse
    {
        $project = $this->projectRepository->find($payload->projectId);
        if (null === $project) {
            throw $this->createNotFoundException('Проект не найден');
        }

        $category = null;
        if (null !== $payload->categoryId) {
            $category = $this->categoryRepository->find($payload->categoryId);
            if (null === $category) {
                throw $this->createNotFoundException('Категория не найдена');
            }
        }

        $task = new Task();
        $task->setTitle($payload->title);
        $task->setDescription($payload->description);
        $task->setProject($project);
        $task->setCategory($category);

        if (null !== $payload->status) {
            $task->setStatus($payload->status);
        }

        if (null !== $payload->priority) {
            $task->setPriority($payload->priority);
        }

        if (null !== $payload->dueDate) {
            $task->setDueDate(new \DateTimeImmutable($payload->dueDate));
        }

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return new JsonResponse([
            'status' => 'success',
            'message' => 'Задача успешно создана',
            'task' => $this->normalizeTask($task),
        ], Response::HTTP_CREATED);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeTask(Task $task): array
    {
        return [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus()->value,
            'priority' => $task->getPriority()->value,
            'due_date' => $task->getDueDate()?->format('Y-m-d'),
            'created_at' => $task->getCreatedAt()->format(\DATE_ATOM),
            'project' => [
                'id' => $task->getProject()->getId(),
                'name' => $task->getProject()->getName(),
            ],
            'category' => null === $task->getCategory() ? null : [
                'id' => $task->getCategory()->getId(),
                'name' => $task->getCategory()->getName(),
            ],
        ];
    }
}
