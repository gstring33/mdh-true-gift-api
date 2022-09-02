<?php

namespace App\Controller;

use App\Model\ChangePasswordModel;
use App\Model\TokenMailModel;
use App\Repository\UserRepository;
use App\Services\Decoder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    private UserRepository $userRepository;
    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;
    private Decoder $decoder;

    public function __construct(
        UserRepository $userRepository,
        ManagerRegistry $doctrine,
        UserPasswordHasherInterface $passwordHasher,
        Decoder $decoder
    ){
        $this->userRepository = $userRepository;
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
        $this->decoder = $decoder;
    }

    #[Route('/api/change-password', name: 'app_security_change-password', methods: ['POST'])]
    public function changePassword(Request $request): JsonResponse
    {
        $queryToken = $request->query->get('t');
        if (!$queryToken) {
            return $this->json(['error' => 'Invalid query'],400);
        }
        $decodedQueryToken = base64_decode($queryToken);
        /** @var TokenMailModel $decodedData */
        $decodedData = $this->decoder->jsonDecode($decodedQueryToken, TokenMailModel::class);
        $now = date('Y-m-d H-m-s');
        if ($now > $decodedData->exp) {
            return $this->json(['error' => 'Date expired. You can not confirm your profil'],400);
        }

        $data = $request->getContent();
        /** @var  ChangePasswordModel $changePasswordModel */
        $changePasswordModel = $this->decoder->jsonDecode($data, ChangePasswordModel::class);
        if ($changePasswordModel->isNewPasswordValid()) {
            $user = $this->userRepository->findOneBy(['uuid' => $decodedData->uuid]);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $changePasswordModel->newPassword
            );
            $user->setPassword($hashedPassword);
            $user->setIsActive(1);
            $user->setProfilConfirmationExpiresAt(null);
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();

            return $this->json(['Password changed successfully']);
        }

        return $this->json(['Password has not been changed'],400);
    }
}
