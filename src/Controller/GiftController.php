<?php

namespace App\Controller;

use App\Entity\Gift;
use App\Model\GiftModel;
use App\Repository\GiftRepository;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/gift', name: 'app_gift')]
class GiftController extends AbstractController
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

    #[Route('/list', name: 'app_gift_all', methods: ['GET'])]
    #[ParamConverter('gift', class: 'App\Request\ParamConverter\GiftConverter')]
    public function all(SerializerInterface $serializer): JsonResponse
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorage->getToken());
        $uuid = $decodedJwtToken['uuid'];
        $currentUser = $this->userRepository->findOneBy(['uuid' => $uuid]);
        $list = $currentUser->getGiftList()->getGifts();
        $json = $serializer->serialize(
            $list->toArray(), 'json',
            SerializationContext::create()
                ->setGroups(['list'])
                ->setSerializeNull(true)
        );
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/partner/list', name: 'app_gift_all_from_partner', methods: ['GET'])]
    #[ParamConverter('gift', class: 'App\Request\ParamConverter\GiftConverter')]
    public function allFromPartner(SerializerInterface $serializer): JsonResponse
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorage->getToken());
        $uuid = $decodedJwtToken['uuid'];
        $currentUser = $this->userRepository->findOneBy(['uuid' => $uuid]);
        $partner = $currentUser->getOfferGiftTo();
        $list =$partner->getGiftList()->getGifts();
        $json = $serializer->serialize($list->toArray(), 'json', SerializationContext::create()->setGroups(['list']));
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('', name: 'app_gift_create', methods: ['POST'])]
    #[ParamConverter('new-gift', class: 'App\Request\ParamConverter\NewGiftConverter')]
    public function createGift(
        Request $request,
        SerializerInterface $serializer
    ): JsonResponse {
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

        $error = $this->validateGift($gift);
        if (!empty($error)) {
            return $this->json($error, 400);
        }

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

        $error = $this->validateGift($gift);
        if (!empty($error)) {
            return $this->json($error, 400);
        }

        $em = $this->doctrine->getManager();
        $em->persist($gift);
        $em->flush();

        $json = $serializer->serialize($gift, 'json', SerializationContext::create()->setGroups(['gift']));
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/{uuid}', name: 'app_gift_delete', methods: ['DELETE'])]
    #[ParamConverter('gift', class: 'App\Request\ParamConverter\GiftConverter')]
    public function delete(
        Request $request,
    ): JsonResponse
    {
        /** @var Gift $gift */
        $gift = $request->attributes->get('gift');
        if (!$gift) {
            return $this->json('Gift with id already deleted');
        }
        $em = $this->doctrine->getManager();
        $em->remove($gift);
        $em->flush();

        return $this->json('Gift with id ' . $gift->getUuid() . ' successfully deleted');
    }

    private function validateGift(Gift $gift)
    {
        $errors = $this->validator->validate($gift);
        if (count($errors) > 0) {
            return ['error' => $errors[0]->getMessage()];
        }
        return [];
    }
}
