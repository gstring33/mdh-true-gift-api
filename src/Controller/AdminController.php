<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\Mailer\MailerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


#[Route('/api/admin', name: 'app_admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    private ManagerRegistry $doctrine;
    private UserRepository $userRepository;

    public function __construct(ManagerRegistry $doctrine, UserRepository $userRepository)
    {
        $this->doctrine = $doctrine;
        $this->userRepository = $userRepository;
    }
    #[Route('/user', name: 'app_admin_all_user', methods: ['GET'])]
    public function getAll()
    {
        $users = $this->userRepository->findAll();

        if (!$users) {
            return $this->json(['message'=> 'User list not Found'], 404);
        }

        return $this->json($users);
    }

    #[Route('/user/{uuid}', name: 'app_admin_user_single', methods: ['GET'])]
    #[ParamConverter('user', class: 'App\Request\ParamConverter\UserConverter')]
    public function getOneByUuid(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('user');
        if (!$user) {
            return $this->json(['message'=> 'No User Found'], 404);
        }

        return $this->json($user);
    }

    #[Route('/user', name: 'app_admin_create_user', methods: ['POST'])]
    #[ParamConverter('new-user', class: 'App\Request\ParamConverter\UserConverter')]
    public function createUser(Request $request, MailerInterface $mailer): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('new-user');
        $em = $this->doctrine->getManager();
        $em->persist($user);
        $em->flush();

        $content = json_decode($request->getContent(), true);
        $body = $this->renderView(
            'emails/user_created.html.twig',
            [
                'email' => $user->getEmail(),
                'firstname' => ucfirst($user->getFirstname()),
                'password' => $content['password']
            ]
        );

        $mailer->send(
            [$user->getEmail()],
            'Herzliche Wilkommen bei True-Gift',
            $body
        );

        return $this->json($user);
    }
}
