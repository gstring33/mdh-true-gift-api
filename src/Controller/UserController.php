<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'app_user')]
class UserController extends AbstractController
{
    #[Route('/user/{uuid}', name: 'app_user_show', methods: ['GET'])]
    public function show(int $uuid): JsonResponse
    {
        $data = [
            'id' => $uuid,
            'name' => 'John Doe'
        ];

        return $this->json($data);
    }

    #[Route('/user', name: 'app_user_create', methods: ['POST'])]
    public function create(): JsonResponse
    {
        return $this->json(['User created succesfully']);
    }

    #[Route('/user/{uuid}', name: 'app_user_edit', methods: ['PUT'])]
    public function edit(int $uuid): JsonResponse
    {
        $data = [
            'id' => $uuid,
            'name' => 'John Doe'
        ];
        return $this->json($data);
    }

    #[Route('/user/{uuid}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete(int $uuid): JsonResponse
    {
        return $this->json('User with id ' . $uuid . ' successfully deleted');
    }
}
