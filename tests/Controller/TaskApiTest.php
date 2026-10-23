<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Task;
use App\Enum\TaskStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskApiTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        (new AppFixtures())->load($this->entityManager);
    }

    public function testListReturnsTasksFromDatabase(): void
    {
        $client = self::getClient();
        $client->request('GET', '/api/tasks');

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame('success', $payload['status']);
        self::assertCount(12, $payload['tasks']);
        self::assertSame('Спроектировать схему базы данных', $payload['tasks'][0]['title']);
        self::assertSame('Task Tracker API', $payload['tasks'][0]['project']['name']);
        self::assertSame('Backend', $payload['tasks'][0]['category']['name']);
    }

    public function testShowReturnsTaskById(): void
    {
        $client = self::getClient();
        $client->request('GET', '/api/tasks/2');

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame('Реализовать CRUD задач', $payload['task']['title']);
        self::assertSame('In Progress', $payload['task']['status']);
    }

    public function testShowReturns404ForUnknownTask(): void
    {
        $client = self::getClient();
        $client->request('GET', '/api/tasks/999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testCreatePersistsTaskWithRelations(): void
    {
        $client = self::getClient();
        $client->jsonRequest('POST', '/api/tasks', [
            'title' => 'Проверить API через Postman',
            'description' => 'Новая задача из JSON-запроса',
            'status' => 'To Do',
            'priority' => 'High',
            'projectId' => 1,
            'categoryId' => 1,
            'dueDate' => '2026-12-15',
        ]);

        self::assertResponseStatusCodeSame(201);

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame('Проверить API через Postman', $payload['task']['title']);
        self::assertSame('Task Tracker API', $payload['task']['project']['name']);
        self::assertSame('Backend', $payload['task']['category']['name']);

        $this->entityManager->clear();
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Проверить API через Postman']);

        self::assertNotNull($task);
        self::assertSame(TaskStatus::Todo, $task->getStatus());
        self::assertSame('2026-12-15', $task->getDueDate()?->format('Y-m-d'));
    }

    public function testCreateRejectsInvalidPayload(): void
    {
        $client = self::getClient();
        $client->jsonRequest('POST', '/api/tasks', [
            'title' => '',
            'projectId' => 1,
        ]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testCreateReturns404ForUnknownProject(): void
    {
        $client = self::getClient();
        $client->jsonRequest('POST', '/api/tasks', [
            'title' => 'Задача без проекта',
            'projectId' => 999,
        ]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testSearchFiltersByTitleAndStatus(): void
    {
        $client = self::getClient();
        $client->request('GET', '/api/tasks/search', ['query' => 'CRUD', 'status' => 'In Progress']);

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        self::assertCount(1, $payload['results']);
        self::assertSame('Реализовать CRUD задач', $payload['results'][0]['title']);
    }
}
