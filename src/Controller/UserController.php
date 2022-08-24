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


#[Route('/api/user', name: 'app_user')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
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

    #[Route('/', name: 'app_user_create', methods: ['POST'])]
    #[ParamConverter('user', class: 'App\Request\ParamConverter\UserConverter')]
    public function create(
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

    #[Route('/{uuid}', name: 'app_user_edit', methods: ['PUT'])]
    public function edit(int $uuid): JsonResponse
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
