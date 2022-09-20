<?php

namespace App\Controller;

use App\Entity\Gift;
use App\Model\GiftModel;
use App\Repository\UserRepository;
use App\Services\Decoder;
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
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;


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

    #[Route('/', name: 'app_gift_all', methods: ['GET'])]
    #[ParamConverter('gift', class: 'App\Request\ParamConverter\GiftConverter')]
    public function all(): JsonResponse
    {
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
    #[ParamConverter('gift', class: 'App\Request\ParamConverter\GiftConverter')]
    public function edit(
        Request $request,
        SerializerInterface $serializer,
        Decoder $decoder,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var Gift $gift */
        $gift = $request->attributes->get('gift');

        if (!$gift) {
            return $this->json(['message'=> 'No Gift Found'], 404);
        }

        $data = $request->getContent();
        $giftModel = $decoder->jsonDecode($data, GiftModel::class);
        $gift
            ->setTitle($giftModel->title)
            ->setDescription($giftModel->description)
            ->setLink($giftModel->link);

        $errors = $validator->validate($gift);
        if (count($errors) > 0) {
            return $this->json(['error' => $errors[0]->getMessage()], 400);
        }

        $em = $this->doctrine->getManager();
        $em->persist($gift);
        $em->flush();

        $json = $serializer->serialize($gift, 'json', SerializationContext::create()->setGroups(['gift']));
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/{uuid}', name: 'app_gift_delete', methods: ['DELETE'])]
    public function delete(int $uuid): JsonResponse
    {
        return $this->json('Gift with id ' . $uuid . ' successfully deleted');
    }
}
