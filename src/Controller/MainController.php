<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class MainController
{
    #[Route('/', name: 'home')]
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'success',
            'message' => 'Добро пожаловать в API системы'
        ]);
    }
}
