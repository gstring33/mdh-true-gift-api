<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/gift', name: 'app_gift')]
class GiftController extends AbstractController
{
    #[Route('/{uuid}', name: 'app_gift_show', methods: ['GET'])]
    public function show(int $uuid): JsonResponse
    {
        $data = [
            'id' => $uuid,
            'title' => 'A Book'
        ];

        return $this->json($data);
    }

    #[Route('/', name: 'app_gift_create', methods: ['POST'])]
    public function create(): JsonResponse
    {
        return $this->json(['Gift created succesfully']);
    }

    #[Route('/{uuid}', name: 'app_gift_edit', methods: ['PUT'])]
    public function edit(int $uuid): JsonResponse
    {
        $data = [
            'id' => $uuid,
            'name' => 'A new Book'
        ];
        return $this->json($data);
    }

    #[Route('/{uuid}', name: 'app_gift_delete', methods: ['DELETE'])]
    public function delete(int $uuid): JsonResponse
    {
        return $this->json('Gift with id ' . $uuid . ' successfully deleted');
    }
}
