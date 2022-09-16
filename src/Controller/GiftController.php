<?php

namespace App\Controller;

use App\Entity\Gift;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


#[Route('/api/gift', name: 'app_gift')]
class GiftController extends AbstractController
{
    private ManagerRegistry $doctrine;
    private JWTTokenManagerInterface $jwtManager;
    private TokenStorageInterface $tokenStorage;
    private UserRepository $userRepository;

    public function __construct(
        ManagerRegistry $doctrine,
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository

    ) {
        $this->doctrine = $doctrine;
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
    }

    #[Route('/{uuid}', name: 'app_gift_show', methods: ['GET'])]
    public function show(int $uuid): JsonResponse
    {
        $data = [
            'id' => $uuid,
            'title' => 'A Book'
        ];

        return $this->json($data);
    }

    #[Route('', name: 'app_gift_create', methods: ['POST'])]
    #[ParamConverter('new-gift', class: 'App\Request\ParamConverter\NewGiftConverter')]
    public function createGift(Request $request, SerializerInterface $serializer): JsonResponse
    {
        /** @var Gift $gift */
        $gift = $request->attributes->get('new-gift');
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorage->getToken());
        $uuid = $decodedJwtToken['uuid'];
        $currentUser = $this->userRepository->findOneBy(['uuid' => $uuid]);

        if (!$currentUser) {
            return $this->json(['message'=> 'No User Found'], 404);
        }

        $list = $currentUser->getGiftList();
        $gift->setGiftList($list);
        $em = $this->doctrine->getManager();
        $em->persist($gift);
        $em->flush();

        $json = $serializer->serialize($gift, 'json', SerializationContext::create()->setGroups(['gift']));
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/{uuid}', name: 'app_gift_edit', methods: ['PUT'])]
    public function edit(int $uuid): JsonResponse
    {
        $data = [
            'id' => $uuid,
            'name' => 'A new Book'
        ];
        return $this->json($data);
    }

    #[Route('/{uuid}', name: 'app_gift_delete', methods: ['DELETE'])]
    public function delete(int $uuid): JsonResponse
    {
        return $this->json('Gift with id ' . $uuid . ' successfully deleted');
    }
}
