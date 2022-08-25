<?php

namespace App\Controller;

use App\Model\ChangePasswordModel;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Karriere\JsonDecoder\JsonDecoder;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityController extends AbstractController
{
    private TokenStorageInterface $tokenStorageInterface;
    private JWTTokenManagerInterface $jwtManager;
    private UserRepository $userRepository;
    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        TokenStorageInterface $tokenStorageInterface,
        JWTTokenManagerInterface $jwtManager,
        UserRepository $userRepository,
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordHasher
    ){
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->jwtManager = $jwtManager;
        $this->userRepository = $userRepository;
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/api/change-password', name: 'app_security_change-password', methods: ['POST'])]
    public function changePassword(Request $request): JsonResponse
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $user = $this->userRepository->findOneBy(['uuid' => $decodedJwtToken['uuid']]);
        $jsonDecoder = new JsonDecoder();
        $data = $request->getContent();
        /** @var  ChangePasswordModel $changePasswordModel */
        $changePasswordModel = $jsonDecoder->decode($data, ChangePasswordModel::class);
        if ($changePasswordModel->isNewPasswordValid()) {
           $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $changePasswordModel->newPassword
            );
            $user->setPassword($hashedPassword);
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();

            return $this->json(['Password changed successfully']);
        }

        return $this->json(['Password has not been changed'],400);
    }
}
