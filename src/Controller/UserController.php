<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


#[Route('/api/user', name: 'app_user', methods:['POST'])]
class UserController extends AbstractController
{
    private JWTTokenManagerInterface $jwtManager;
    private TokenStorageInterface $tokenStorage;
    private UserRepository $userRepository;
    private ManagerRegistry $doctrine;


    public function __construct(
        ManagerRegistry $doctrine,
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository,
    ) {
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
        $this->doctrine = $doctrine;
    }

    #[Route('/select-partner', name: 'app_user_select')]
    public function select(): JsonResponse
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorage->getToken());
        $uuid = $decodedJwtToken['uuid'];
        $currentUser = $this->userRepository->findOneBy(['uuid' => $uuid]);

        if (!$currentUser || !$currentUser->isActive()) {
            return $this->json(['message'=> 'User not found or inactive'], 404);
        }

        /** @var User|null $partner */
        $partner = $this->userRepository->selectPartner($currentUser);
        if ($partner == null) {
            return $this->json(['message' => 'No user selected'], 400);
        }

        $currentUser->setOfferGiftTo($partner);
        $partner->setRecieveGiftFrom($currentUser);
        $em = $this->doctrine->getManager();
        $em->persist($currentUser);
        $em->persist($partner);
        $em->flush();

        return $this->json($partner);
    }
}
