<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Project;
use App\Repository\CategoryRepository;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends AbstractController
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    #[Route('/api/projects', name: 'project_list', methods: ['GET'])]
    public function projects(): JsonResponse
    {
        $projects = $this->projectRepository->findBy([], ['id' => 'ASC']);

        return new JsonResponse([
            'status' => 'success',
            'projects' => array_map(static fn (Project $project): array => [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'description' => $project->getDescription(),
            ], $projects),
        ]);
    }

    #[Route('/projects', name: 'project_index', methods: ['GET'])]
    public function projectsPage(): Response
    {
        $projects = $this->projectRepository->findBy([], ['id' => 'ASC']);

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/categories', name: 'category_index', methods: ['GET'])]
    public function categoriesPage(): Response
    {
        $categories = $this->categoryRepository->findBy([], ['id' => 'ASC']);

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/api/categories', name: 'category_list', methods: ['GET'])]
    public function categories(): JsonResponse
    {
        $categories = $this->categoryRepository->findBy([], ['id' => 'ASC']);

        return new JsonResponse([
            'status' => 'success',
            'categories' => array_map(static fn (Category $category): array => [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'description' => $category->getDescription(),
            ], $categories),
        ]);
    }
}
