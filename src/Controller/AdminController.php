<?php

namespace App\Controller;

use App\Entity\GiftList;
use App\Entity\User;
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

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
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
    #[ParamConverter('user', class: 'App\Request\ParamConverter\UserConverter')]
    public function createUser(Request $request, MailerInterface $mailer): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('user');
        $list = new GiftList();
        $list->setIsPublished(false);
        $list->setUuid(uniqid('', false));
        $user->setGiftList($list);
        $em = $this->doctrine->getManager();
        $em->persist($user);
        $em->flush();

        $mail =$mailer->send(
            [$user->getEmail()],
            'Herzliche Wilkommen bei True-Gift',
            'Test'
        );

        return $this->json($user);
    }
}
