<?php

namespace App\Request\ParamConverter;

use App\Entity\Gift;
use App\Entity\User;
use App\Model\GiftModel;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Services\Decoder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NewGiftConverter implements ParamConverterInterface
{
    private Decoder $decoder;
    private JWTTokenManagerInterface $jwtManager;
    private TokenStorageInterface $tokenStorage;
    private UserRepository $userRepository;

    public function __construct (
        Decoder $decoder,
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository
    ) {

        $this->decoder = $decoder;
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $gift = $this->convertNewGift($request);
        $request->attributes->set($configuration->getName(), $gift);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getName() === 'new-gift';
    }

    public function convertNewGift($request)
    {
        /** @var GiftModel $giftModel */
        $giftModel = $this->decoder->jsonDecode($request->getContent(), GiftModel::class);
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorage->getToken());
        $uuid = $decodedJwtToken['uuid'];
        /** @var User $currentUser */
        $currentUser = $this->userRepository->findOneBy(['uuid' => $uuid]);

        return (new Gift())
            ->setUuid(uniqid('', false))
            ->setTitle($giftModel->title)
            ->setDescription($giftModel->description)
            ->setLink($giftModel->link);
    }
}