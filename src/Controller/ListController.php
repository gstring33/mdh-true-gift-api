<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/list', name: 'app_list')]
class ListController extends AbstractController
{
    #[Route('/{uuid}', name: 'app_list_show', methods: ['GET'])]
    public function show(int $uuid): JsonResponse
    {
        $data = [
            'id' => $uuid,
            'isPublished' => true
        ];

        return $this->json($data);
    }

    #[Route('/{uuid}', name: 'app_list_delete', methods: ['DELETE'])]
    public function delete(int $uuid): JsonResponse
    {
        return $this->json('List with id ' . $uuid . ' successfully deleted');
    }
}
