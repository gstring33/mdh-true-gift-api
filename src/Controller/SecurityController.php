<?php

namespace App\Controller;

use App\Entity\User;
use App\Model\ChangePasswordModel;
use App\Repository\UserRepository;
use App\Services\Decoder;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SecurityController extends AbstractController
{
    private UserRepository $userRepository;
    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;
    private Decoder $decoder;
    private TokenStorageInterface $tokenStorage;
    private JWTTokenManagerInterface $tokenManager;

    public function __construct(
        UserRepository $userRepository,
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordHasher,
        Decoder $decoder,
        TokenStorageInterface $tokenStorage,
        JWTTokenManagerInterface $tokenManager
    ){
        $this->userRepository = $userRepository;
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
        $this->decoder = $decoder;
        $this->tokenStorage = $tokenStorage;
        $this->tokenManager = $tokenManager;
    }

    #[Route('/api/change-password', name: 'app_security_change-password', methods: ['POST'])]
    public function changePassword(Request $request): JsonResponse
    {
        $token = $this->tokenManager->decode($this->tokenStorage->getToken());
        $user = $this->userRepository->findOneBy(['uuid' => $token['uuid']]);
        if (!$user instanceof User) {
            return $this->json(['Password has not been changed : User does not exist'],400);
        }

        $data = $request->getContent();
        /** @var  ChangePasswordModel $changePasswordModel */
        $changePasswordModel = $this->decoder->jsonDecode($data, ChangePasswordModel::class);
        if ($changePasswordModel->isNewPasswordValid()) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $changePasswordModel->newPassword
            );
            $user->setPassword($hashedPassword);
            $user->setIsActive(1);
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();

            return $this->json('Password changed successfully');
        }

        return $this->json('Password has not been changed',400);
    }
}
