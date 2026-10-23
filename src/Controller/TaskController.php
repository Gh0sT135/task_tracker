<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TaskController
{
    private array $tasks = [
        [
            'id' => 1,
            'title' => 'Разработать авторизацию',
            'status' => 'In Progress',
            'priority' => 'High',
            'project_id' => 1,
        ],
        [
            'id' => 2,
            'title' => 'Создать главную страницу',
            'status' => 'To Do',
            'priority' => 'Medium',
            'project_id' => 1,
        ],
        [
            'id' => 3,
            'title' => 'Подготовить документацию API',
            'status' => 'Done',
            'priority' => 'Low',
            'project_id' => 2,
        ],
    ];

    // Список задач
    #[Route('/api/tasks', name: 'task_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'tasks' => $this->tasks,
        ]);
    }

    // Получение одной задачи по ID
    #[Route(
        '/api/tasks/{id}',
        name: 'task_show',
        methods: ['GET'],
        requirements: ['id' => '\d+']
    )]
    public function show(int $id): JsonResponse
    {
        foreach ($this->tasks as $task) {
            if ($task['id'] === $id) {
                return new JsonResponse([
                    'status' => 'success',
                    'task' => $task,
                ]);
            }
        }

        return new JsonResponse([
            'status' => 'error',
            'message' => 'Задача не найдена',
        ], 404);
    }

    // Фильтрация задач
    #[Route('/api/tasks/search', name: 'task_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $searchQuery = (string) $request->query->get('query', 'all');

        return new JsonResponse([
            'status' => 'success',
            'search_term' => $searchQuery,
            'results' => [
                [
                    'id' => 1,
                    'title' => 'Разработать авторизацию',
                ],
                [
                    'id' => 2,
                    'title' => 'Создать главную страницу',
                ],
            ],
        ]);
    }

    // Создание задачи
    #[Route('/api/tasks', name: 'task_create', methods: ['POST'])]
    public function create(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'message' => 'Задача успешно создана',
            'task' => [
                'id' => 4,
                'title' => 'Новая задача',
                'status' => 'To Do',
                'priority' => 'Medium',
                'project_id' => 1,
            ],
        ], 201);
    }
}
