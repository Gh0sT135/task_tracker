<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskWebTest extends WebTestCase
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

    public function testIndexRendersTasksFromDatabase(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', '/tasks');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Список задач');
        self::assertCount(12, $crawler->filter('table tbody tr'));
        self::assertSelectorTextContains('table tbody', 'Спроектировать схему базы данных');
        self::assertSelectorTextContains('table tbody', 'Task Tracker API');
    }

    public function testNewRendersForm(): void
    {
        $client = self::getClient();
        $client->request('GET', '/tasks/new');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Создать новую задачу');
        self::assertSelectorExists('form input[name="task[title]"]');
        self::assertSelectorExists('form select[name="task[project]"]');
        self::assertSelectorExists('form select[name="task[status]"]');
    }

    public function testNewPersistsTaskAndRedirectsWithFlash(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', '/tasks/new');

        $form = $crawler->selectButton('Сохранить')->form([
            'task[title]' => 'Проверить веб-форму',
            'task[description]' => 'Задача, созданная через Twig-форму',
            'task[project]' => '1',
            'task[category]' => '1',
            'task[status]' => 'In Progress',
            'task[priority]' => 'High',
            'task[dueDate]' => '2026-12-15',
        ]);
        $client->submit($form);

        self::assertResponseRedirects('/tasks');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Задача успешно добавлена!');

        $this->entityManager->clear();
        $task = $this->entityManager->getRepository(Task::class)->findOneBy(['title' => 'Проверить веб-форму']);

        self::assertNotNull($task);
        self::assertSame('2026-12-15', $task->getDueDate()?->format('Y-m-d'));
        self::assertSame('In Progress', $task->getStatus()->value);
    }

    public function testNewRejectsEmptyTitle(): void
    {
        $client = self::getClient();
        $crawler = $client->request('GET', '/tasks/new');

        $form = $crawler->selectButton('Сохранить')->form([
            'task[title]' => '',
            'task[project]' => '1',
        ]);
        $client->submit($form);

        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextContains('.text-danger', 'This value should not be blank.');

        $this->entityManager->clear();
        self::assertNull($this->entityManager->getRepository(Task::class)->findOneBy(['title' => '']));
    }

    public function testCatalogPagesAreRendered(): void
    {
        $client = self::getClient();

        $client->request('GET', '/categories');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Справочник категорий');
        self::assertSelectorTextContains('table tbody', 'Backend');

        $client->request('GET', '/projects');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Список проектов');
        self::assertSelectorTextContains('table tbody', 'Task Tracker API');
    }
}
