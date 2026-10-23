<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Project;
use App\Entity\Task;
use App\Enum\TaskPriority;
use App\Enum\TaskStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [];
        foreach ([
            'Backend' => 'Серверная логика, API и база данных',
            'Frontend' => 'Пользовательский интерфейс и клиентская логика',
            'Инфраструктура' => 'Серверы, деплой и мониторинг',
            'Документация' => 'Техническая и пользовательская документация',
        ] as $name => $description) {
            $category = new Category();
            $category->setName($name);
            $category->setDescription($description);

            $manager->persist($category);
            $categories[$name] = $category;
        }

        $projects = [];
        foreach ([
            'Task Tracker API' => 'REST API трекера задач',
            'Личный кабинет' => 'Веб-интерфейс для клиентов',
            'Платёжный модуль' => 'Интеграция с платёжным провайдером',
            'Внутренние инструменты' => 'Автоматизация процессов команды',
        ] as $name => $description) {
            $project = new Project();
            $project->setName($name);
            $project->setDescription($description);

            $manager->persist($project);
            $projects[$name] = $project;
        }

        $rows = [
            ['Спроектировать схему базы данных', 'Task Tracker API', 'Backend', TaskStatus::Done, TaskPriority::High, '2026-10-10'],
            ['Реализовать CRUD задач', 'Task Tracker API', 'Backend', TaskStatus::InProgress, TaskPriority::High, '2026-11-05'],
            ['Настроить миграции Doctrine', 'Task Tracker API', 'Backend', TaskStatus::Done, TaskPriority::Medium, '2026-10-15'],
            ['Добавить фильтрацию задач', 'Task Tracker API', 'Backend', TaskStatus::Todo, TaskPriority::Medium, '2026-11-20'],
            ['Сверстать список проектов', 'Личный кабинет', 'Frontend', TaskStatus::InProgress, TaskPriority::Medium, '2026-11-10'],
            ['Добавить форму создания задачи', 'Личный кабинет', 'Frontend', TaskStatus::Todo, TaskPriority::High, '2026-11-25'],
            ['Исправить отображение на мобильных', 'Личный кабинет', 'Frontend', TaskStatus::Review, TaskPriority::Low, '2026-10-30'],
            ['Подключить платёжный шлюз', 'Платёжный модуль', 'Backend', TaskStatus::InProgress, TaskPriority::Critical, '2026-11-01'],
            ['Написать тесты для оплаты', 'Платёжный модуль', 'Backend', TaskStatus::Todo, TaskPriority::High, '2026-11-15'],
            ['Настроить мониторинг серверов', 'Внутренние инструменты', 'Инфраструктура', TaskStatus::Todo, TaskPriority::Medium, '2026-12-01'],
            ['Описать API в OpenAPI', 'Task Tracker API', 'Документация', TaskStatus::Review, TaskPriority::Low, '2026-11-30'],
            ['Составить инструкцию для команды', 'Внутренние инструменты', 'Документация', TaskStatus::Done, TaskPriority::Low, '2026-10-20'],
        ];

        foreach ($rows as [$title, $projectName, $categoryName, $status, $priority, $dueDate]) {
            $task = new Task();
            $task->setTitle($title);
            $task->setDescription(sprintf('Задача проекта «%s» в категории «%s».', $projectName, $categoryName));
            $task->setProject($projects[$projectName]);
            $task->setCategory($categories[$categoryName]);
            $task->setStatus($status);
            $task->setPriority($priority);
            $task->setDueDate(new \DateTimeImmutable($dueDate));

            $manager->persist($task);
        }

        $manager->flush();
    }
}
