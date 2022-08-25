<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


#[Route('/api/admin', name: 'app_admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/{uuid}', name: 'app_user_show', methods: ['GET'])]
    public function show(int $uuid): JsonResponse
    {
        $data = [
            'id' => $uuid,
            'name' => 'John Doe'
        ];

        return $this->json($data);
    }

    #[Route('/', name: 'app_admin_create_user', methods: ['POST'])]
    #[ParamConverter('user', class: 'App\Request\ParamConverter\UserConverter')]
    public function createUser(
        ManagerRegistry $doctrine,
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('user');
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $user->getPlainTextPassword()
        );
        $user->setPassword($hashedPassword);
        $em = $doctrine->getManager();
        $em->persist($user);
        $em->flush();

        return $this->json(['User created succesfully']);
    }

    #[Route('/{uuid}', name: 'app_admin_edit_user', methods: ['PUT'])]
    public function editUser(int $uuid): JsonResponse
    {
        $data = [
            'id' => $uuid,
            'name' => 'John Doe'
        ];
        return $this->json($data);
    }

    #[Route('/{uuid}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete(int $uuid): JsonResponse
    {
        return $this->json('User with id ' . $uuid . ' successfully deleted');
    }
}
