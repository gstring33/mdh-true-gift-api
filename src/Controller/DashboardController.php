<?php

namespace App\Controller;

use App\Repository\GiftRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DashboardController extends AbstractController
{
    private ManagerRegistry $doctrine;
    private JWTTokenManagerInterface $jwtManager;
    private TokenStorageInterface $tokenStorage;
    private UserRepository $userRepository;
    private GiftRepository $giftRepository;
    private ValidatorInterface $validator;

    public function __construct(
        ManagerRegistry $doctrine,
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository,
        GiftRepository $giftRepository,
        ValidatorInterface $validator

    ) {
        $this->doctrine = $doctrine;
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
        $this->giftRepository = $giftRepository;
        $this->validator = $validator;
    }
    #[Route('/api/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function show(SerializerInterface $serializer): JsonResponse
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorage->getToken());
        $uuid = $decodedJwtToken['uuid'];
        $currentUser = $this->userRepository->findOneBy(['uuid' => $uuid]);

        $json = $serializer->serialize($currentUser, 'json', SerializationContext::create()->setGroups(['dashboard']));
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/api/dashboard/users', name: 'app_dashboard_users', methods: ['GET'])]
    public function allUsers(SerializerInterface $serializer): JsonResponse
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorage->getToken());
        $uuid = $decodedJwtToken['uuid'];
        $currentUser = $this->userRepository->findOneBy(['uuid' => $uuid]);
        $users = $this->userRepository->findAllOtherUsers($currentUser);

        $json = $serializer->serialize($users, 'json', SerializationContext::create()->setGroups(['dashboard_users']));
        return new JsonResponse($json, 200, [], true);
    }
}