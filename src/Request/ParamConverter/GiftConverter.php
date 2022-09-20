<?php

namespace App\Request\ParamConverter;

use App\Repository\GiftRepository;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GiftConverter implements ParamConverterInterface
{
    private GiftRepository $giftRepository;
    private JWTTokenManagerInterface $jwtManager;
    private TokenStorageInterface $tokenStorage;
    private UserRepository $userRepository;

    public function __construct(
        GiftRepository $giftRepository,
        UserRepository $userRepository,
        JWTTokenManagerInterface $jwtManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->giftRepository = $giftRepository;
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
        $this->userRepository = $userRepository;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorage->getToken());
        $userUuid = $decodedJwtToken['uuid'];
        $giftUuid = $request->attributes->get('uuid');
        $list = $this->userRepository->findOneBy(['uuid' => $userUuid])->getGiftList();
        $gift = $this->giftRepository->findOneBy(['uuid' => $giftUuid, 'giftList' => $list]);

        $request->attributes->set($configuration->getName(), $gift);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getName() === 'gift';
    }
}